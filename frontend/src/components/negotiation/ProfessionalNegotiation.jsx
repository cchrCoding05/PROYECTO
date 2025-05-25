import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import { chatService } from '../../services/chatService';
import './Negotiation.css';

const API_URL = 'http://localhost:8000/api';

const ProfessionalNegotiation = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { user } = useAuth();
    const [professional, setProfessional] = useState(null);
    const [negotiations, setNegotiations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const messagesEndRef = useRef(null);
    const [message, setMessage] = useState('');
    const [rating, setRating] = useState(5);
    const [comment, setComment] = useState('');
    const [showRatingModal, setShowRatingModal] = useState(false);
    const [proposedPrice, setProposedPrice] = useState('');
    const [negotiationError, setNegotiationError] = useState(null);
    const [negotiationSuccess, setNegotiationSuccess] = useState(false);
    const [actionMessage, setActionMessage] = useState(null);
    const [sendingMessage, setSendingMessage] = useState(false);

    useEffect(() => {
        const token = localStorage.getItem('token');
        if (!user || !token) {
            navigate('/login');
            return;
        }
        fetchProfessionalData();
        const interval = setInterval(fetchProfessionalData, 30000);
        return () => clearInterval(interval);
    }, [id, user]);

    // FUNCIÓN MEJORADA para normalizar mensajes con consistencia de campos
    const normalizeMessages = (messages) => {
        return (messages || []).map(msg => {
            // Extraer el precio de manera consistente
            const price = msg.price || msg.precioPropuesto || msg.proposedCredits || null;
            
            return {
                ...msg,
                id: msg.id,
                // Unificar campos de precio
                price: price,
                precioPropuesto: price,
                proposedCredits: price,
                // Unificar campos de mensaje
                contenido: msg.contenido || msg.message || null,
                message: msg.contenido || msg.message || null,
                // Usuario
                user_id: msg.user_id || msg.emisor?.id || msg.sender_id,
                user_name: msg.user_name || msg.emisor?.nombreUsuario || msg.senderName,
                // Fechas
                fecha: msg.fecha_envio || msg.created_at || msg.date,
                date: msg.fecha_envio || msg.created_at || msg.date,
                created_at: msg.created_at || msg.fecha_envio || msg.date,
                // Estados
                isBuyer: msg.isBuyer || (msg.user_id === user?.id),
                accepted: msg.accepted || msg.aceptado_comprador || msg.aceptado_vendedor || false,
                rejected: msg.rejected || false,
                estado: msg.estado || 'EN_NEGOCIACION',
                aceptado_comprador: msg.aceptado_comprador || false,
                aceptado_vendedor: msg.aceptado_vendedor || false,
                // Tipo de mensaje
                isChatProposal: !!msg.isChatProposal,
                isMessage: !!(msg.contenido || msg.message),
                isPriceProposal: !!(price !== null && price !== undefined)
            };
        });
    };

    const fetchProfessionalData = async () => {
        try {
            const token = localStorage.getItem('token');
            if (!token) {
                navigate('/login');
                return;
            }

            console.log('=== FETCHING PROFESSIONAL DATA ===');

            // Obtener mensajes del chat
            const chatResponse = await fetch(`${API_URL}/chat/${id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (chatResponse.status === 401) {
                localStorage.removeItem('token');
                navigate('/login');
                return;
            }

            if (!chatResponse.ok) {
                const errorData = await chatResponse.json().catch(() => null);
                console.error('Error en la respuesta del chat:', errorData);
                throw new Error(errorData?.message || 'Error al obtener el chat');
            }

            const chatData = await chatResponse.json();
            console.log('Datos del chat recibidos:', chatData);

            if (!chatData.success) {
                throw new Error(chatData.message || 'Error al obtener datos del chat');
            }

            // Establecer datos del profesional
            setProfessional(chatData.data.professional);

            // Obtener propuestas de precio específicas del chat
            const priceProposalsResponse = await fetch(`${API_URL}/chat/${id}/price-proposals`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            let priceProposalsData = { success: true, data: [] };

            if (priceProposalsResponse.ok) {
                try {
                    priceProposalsData = await priceProposalsResponse.json();
                    console.log('Propuestas de precio recibidas:', priceProposalsData);
                } catch (parseError) {
                    console.warn('Error al parsear propuestas de precio:', parseError);
                }
            } else {
                console.warn('Error al obtener propuestas de precio:', priceProposalsResponse.status);
            }

            // Normalizar mensajes de chat
            const chatMessages = normalizeMessages(
                (chatData.data.messages || []).map(msg => ({
                    ...msg,
                    isMessage: true,
                    isPriceProposal: false
                }))
            );

            // Normalizar propuestas de precio
            const priceProposals = normalizeMessages(
                (priceProposalsData.data || []).map(proposal => ({
                    ...proposal,
                    isMessage: false,
                    isPriceProposal: true,
                    // Asegurar que tiene los campos necesarios
                    price: proposal.price || proposal.precioPropuesto,
                    contenido: null,
                    message: null
                }))
            );

            // Combinar y filtrar duplicados
            const combinedMessages = [...chatMessages, ...priceProposals]
                .filter((msg, index, self) =>
                    index === self.findIndex((m) => 
                        m.id === msg.id && 
                        m.isPriceProposal === msg.isPriceProposal
                    )
                )
                .sort((a, b) => new Date(a.date || 0) - new Date(b.date || 0));

            console.log('Mensajes combinados:', combinedMessages);
            console.log('Propuestas de precio filtradas:', combinedMessages.filter(m => m.isPriceProposal));
            console.log('Mensajes de chat filtrados:', combinedMessages.filter(m => m.isMessage));

            setNegotiations(combinedMessages);

        } catch (error) {
            console.error('Error al cargar datos:', error);
            if (error.message.includes('401')) {
                localStorage.removeItem('token');
                navigate('/login');
            } else {
                setError('Error al cargar la información del profesional: ' + error.message);
            }
        } finally {
            setLoading(false);
        }
    };

    // Función optimizada para actualizar solo los mensajes del chat
    const fetchChatMessages = async () => {
        try {
            const token = localStorage.getItem('token');
            if (!token) return;

            const chatResponse = await fetch(`${API_URL}/chat/${id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!chatResponse.ok) return;

            const chatData = await chatResponse.json();

            if (chatData.success && chatData.data.messages) {
                const newChatMessages = normalizeMessages(
                    chatData.data.messages.map(msg => ({
                        ...msg,
                        isMessage: true,
                        isPriceProposal: false
                    }))
                );

                // Mantener las propuestas existentes y actualizar solo los mensajes
                setNegotiations(prevNegotiations => {
                    const existingPriceProposals = prevNegotiations.filter(msg => msg.isPriceProposal);
                    
                    const combinedMessages = [...newChatMessages, ...existingPriceProposals]
                        .filter((msg, index, self) =>
                            index === self.findIndex((m) => 
                                m.id === msg.id && 
                                m.isPriceProposal === msg.isPriceProposal
                            )
                        )
                        .sort((a, b) => new Date(a.date || 0) - new Date(b.date || 0));

                    return combinedMessages;
                });
            }
        } catch (error) {
            console.error('Error al actualizar mensajes del chat:', error);
        }
    };

    const handleProposePrice = async (e) => {
        e.preventDefault();
        setNegotiationError(null);
        setNegotiationSuccess(false);

        console.log('Iniciando propuesta de precio:', proposedPrice);

        if (!proposedPrice || parseInt(proposedPrice) < 1) {
            setNegotiationError('El monto debe ser al menos 1 punto');
            return;
        }

        if (user && user.credits < parseInt(proposedPrice)) {
            setNegotiationError('No tienes suficientes puntos para ofertar');
            return;
        }

        try {
            const token = localStorage.getItem('token');
            console.log('Enviando propuesta al servidor:', {
                professionalId: id,
                price: parseInt(proposedPrice),
                isChatProposal: true
            });

            const response = await fetch(`${API_URL}/chat/${id}/propose-price`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    price: parseInt(proposedPrice),
                    isChatProposal: true
                })
            });

            const data = await response.json();
            console.log('Respuesta del servidor para propuesta:', data);

            if (data.success) {
                setNegotiationSuccess(true);
                setProposedPrice('');
                // Recargar datos completos para incluir la nueva propuesta
                fetchProfessionalData();
            } else {
                setNegotiationError(data.message || 'Error al proponer precio');
            }
        } catch (error) {
            console.error('Error en la propuesta de precio:', error);
            setNegotiationError('Error al proponer precio');
        }
    };

    const handleRateProfessional = async () => {
        try {
            const token = localStorage.getItem('token');
            console.log('Enviando valoración:', {
                professional_id: id,
                rating: rating,
                comment: comment
            });

            const response = await fetch(`${API_URL}/ratings`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    professional_id: id,
                    rating: rating,
                    comment: comment
                })
            });

            const data = await response.json();
            console.log('Respuesta de valoración:', data);

            if (data.success) {
                setShowRatingModal(false);
                setComment('');
                setRating(5);
                setActionMessage({ type: 'success', text: 'Valoración enviada con éxito' });
                fetchProfessionalData();
            } else {
                if (data.message === 'Ya has valorado a este profesional') {
                    setActionMessage({
                        type: 'warning',
                        text: 'Ya has valorado a este profesional anteriormente'
                    });
                } else {
                    setActionMessage({
                        type: 'danger',
                        text: data.message || 'Error al enviar la valoración'
                    });
                }
            }
        } catch (error) {
            console.error('Error al enviar valoración:', error);
            setActionMessage({ type: 'danger', text: 'Error al enviar la valoración' });
        }
    };

    const handleSendMessage = async (e) => {
        e.preventDefault();
        if (!message.trim()) return;

        try {
            setSendingMessage(true);
            const result = await chatService.sendMessage(id, message);
            console.log('Resultado de envío de mensaje:', result);

            if (result.success) {
                setMessage('');
                await fetchChatMessages();
            } else {
                setError(result.message || 'Error al enviar el mensaje');
            }
        } catch (err) {
            console.error('Error al enviar mensaje:', err);
            setError('Error al enviar el mensaje');
        } finally {
            setSendingMessage(false);
        }
    };

    const handleAcceptOffer = async (negotiationId) => {
        try {
            setActionMessage(null);
            console.log('=== INICIO ACEPTACIÓN DE PROPUESTA ===');
            console.log('ID de la propuesta:', negotiationId);

            const response = await fetch(`${API_URL}/chat/${id}/accept-proposal/${negotiationId}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json'
                }
            });

            console.log('Respuesta del servidor:', {
                status: response.status,
                statusText: response.statusText
            });

            if (!response.ok) {
                throw new Error('Error al aceptar la oferta');
            }

            const data = await response.json();
            console.log('Datos de la respuesta:', data);

            if (data.success) {
                setNegotiations(prevNegotiations => {
                    const updatedNegotiations = prevNegotiations.map(neg => {
                        if (neg.id === negotiationId) {
                            console.log('Actualizando negociación:', {
                                id: neg.id,
                                estadoAnterior: neg.estado,
                                estadoNuevo: data.data.estado
                            });
                            return {
                                ...neg,
                                accepted: data.data.aceptado_comprador || data.data.aceptado_vendedor,
                                estado: data.data.estado,
                                aceptado_comprador: data.data.aceptado_comprador,
                                aceptado_vendedor: data.data.aceptado_vendedor
                            };
                        }
                        return neg;
                    });
                    console.log('Negociaciones actualizadas:', updatedNegotiations);
                    return updatedNegotiations;
                });

                setActionMessage({ type: 'success', text: '¡Oferta aceptada con éxito!' });
                if (data.data.estado === 'finalizada') {
                    setActionMessage({ type: 'success', text: '¡Oferta aceptada y puntos transferidos con éxito!' });
                }
            } else {
                throw new Error(data.message || 'Error al aceptar la oferta');
            }
            console.log('=== FIN ACEPTACIÓN DE PROPUESTA ===');
        } catch (error) {
            console.error('Error al aceptar oferta:', error);
            setActionMessage({ type: 'danger', text: 'Error al aceptar la oferta' });
        }
    };

    const handleRejectOffer = async (negotiationId) => {
        try {
            setActionMessage(null);
            const response = await fetch(`${API_URL}/chat/${id}/reject-proposal/${negotiationId}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Error al rechazar la oferta');
            }

            const data = await response.json();
            if (data.success) {
                setNegotiations(prevNegotiations =>
                    prevNegotiations.map(neg =>
                        neg.id === negotiationId
                            ? { ...neg, accepted: false, rejected: true, estado: 'rechazada' }
                            : neg
                    )
                );

                setActionMessage({ type: 'success', text: '¡Oferta rechazada con éxito!' });
            } else {
                throw new Error(data.message || 'Error al rechazar la oferta');
            }
        } catch (error) {
            console.error('Error al rechazar oferta:', error);
            setActionMessage({ type: 'danger', text: 'Error al rechazar la oferta' });
        }
    };

    const renderMessage = (msg) => {
        if (!msg || !msg.isMessage) return null;
        const isCurrentUser = msg.user_id === user?.id;
        return (
            <div
                key={`message-${msg.id}`}
                className={`message ${isCurrentUser ? 'sent' : 'received'}`}
            >
                <div className="message-content">
                    {msg.contenido || msg.message}
                    <span
                        className="message-time"
                        style={{
                            display: 'block',
                            fontSize: '0.8em',
                            color: '#ccc',
                            marginTop: 4,
                            textAlign: isCurrentUser ? 'right' : 'left'
                        }}
                    >
                        {msg.fecha ? new Date(msg.fecha).toLocaleTimeString() : 'Sin fecha'}
                    </span>
                </div>
            </div>
        );
    };

    if (loading) {
        return (
            <div className="container py-4">
                <div className="text-center">
                    <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="container py-4">
                <AlertMessage message={error} type="danger" />
            </div>
        );
    }

    // Filtrar propuestas de precio para mostrar
    const priceProposals = negotiations.filter(msg => 
        msg.isPriceProposal && 
        msg.price !== null && 
        msg.price !== undefined
    );

    // Filtrar mensajes de chat para mostrar
    const chatMessages = negotiations.filter(msg => 
        msg.isMessage && 
        (msg.contenido || msg.message)
    );

    console.log('Propuestas a renderizar:', priceProposals);
    console.log('Mensajes a renderizar:', chatMessages);

    return (
        <div className="container py-4">
            {actionMessage && (
                <AlertMessage
                    message={actionMessage.text}
                    type={actionMessage.type}
                    onClose={() => setActionMessage(null)}
                />
            )}
            <div className="row">
                <div className="col-md-4">
                    <div className="card shadow-sm mb-4">
                        <div className="card-body">
                            <div className="text-center mb-4">
                                <img
                                    src={professional?.profile_image || '/default-avatar.png'}
                                    alt={professional?.name}
                                    className="rounded-circle mb-3"
                                    style={{ width: '150px', height: '150px', objectFit: 'cover' }}
                                />
                                <h5 className="card-title mb-1">{professional?.name || 'No disponible'}</h5>
                                <p className="text-muted mb-0">{professional?.profession || 'Profesión no especificada'}</p>
                            </div>
                        </div>
                    </div>

                    <div className="card shadow-sm">
                        <div className="card-body">
                            <h5 className="card-title">Proponer precio</h5>
                            {negotiationError && (
                                <AlertMessage
                                    message={negotiationError}
                                    type="danger"
                                />
                            )}
                            {negotiationSuccess && (
                                <AlertMessage
                                    message="Precio propuesto con éxito"
                                    type="success"
                                />
                            )}
                            <form onSubmit={handleProposePrice}>
                                <div className="input-group mb-3">
                                    <input
                                        type="number"
                                        className="form-control"
                                        value={proposedPrice}
                                        onChange={(e) => setProposedPrice(e.target.value)}
                                        placeholder="Ingresa tu oferta en créditos"
                                        min="1"
                                        required
                                    />
                                    <button
                                        className="btn btn-primary"
                                        type="submit"
                                    >
                                        Proponer
                                    </button>
                                </div>
                            </form>

                            {/* Botón de valoración */}
                            {negotiations.some(msg => msg.estado === 'finalizada') && (
                                <div className="card shadow-sm mt-3">
                                    <div className="card-body">
                                        <h5 className="card-title">Valorar Servicio</h5>
                                        <div className="rating-stars mb-3">
                                            {[1, 2, 3, 4, 5].map((star) => (
                                                <i
                                                    key={star}
                                                    className={`bi bi-star${star <= rating ? '-fill' : ''} fs-4 me-2`}
                                                    style={{ cursor: 'pointer', color: star <= rating ? '#ffc107' : '#6c757d' }}
                                                    onClick={() => setRating(star)}
                                                />
                                            ))}
                                        </div>
                                        <textarea
                                            className="form-control mb-3"
                                            value={comment}
                                            onChange={(e) => setComment(e.target.value)}
                                            placeholder="Escribe tu comentario..."
                                            rows="3"
                                        />
                                        <button
                                            className="btn btn-primary w-100"
                                            onClick={handleRateProfessional}
                                            disabled={!comment.trim()}
                                        >
                                            Enviar Valoración
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <div className="col-md-8">
                    <div className="card shadow-sm mb-4">
                        <div className="card-body">
                            <h5 className="card-title mb-4">Propuestas de precio</h5>
                            {priceProposals.length === 0 ? (
                                <p className="text-muted">No hay propuestas de precio aún.</p>
                            ) : (
                                <div className="list-group">
                                    {priceProposals
                                        .sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
                                        .map((msg, index) => (
                                            <div
                                                key={`proposal-${msg.id}-${index}`}
                                                className="list-group-item"
                                            >
                                                <div className="d-flex justify-content-between align-items-start">
                                                    <div className="flex-grow-1">
                                                        <div className="d-flex align-items-center mb-2">
                                                            <h6 className="mb-0 me-2">
                                                                {msg.user_name || 'Usuario'}
                                                            </h6>
                                                            {(msg.estado === 'aceptada' || msg.estado === 'finalizada' || msg.accepted || msg.aceptado_vendedor || msg.aceptado_comprador) && (
                                                                <span className="badge bg-success ms-2">
                                                                    Aceptada
                                                                </span>
                                                            )}
                                                            {(msg.estado === 'rechazada' || msg.rejected) && (
                                                                <span className="badge bg-danger ms-2">
                                                                    Rechazada
                                                                </span>
                                                            )}
                                                            {msg.estado === 'EN_NEGOCIACION' && !msg.accepted && !msg.rejected && !msg.aceptado_vendedor && !msg.aceptado_comprador && (
                                                                <span className="badge bg-primary ms-2">
                                                                    Activa
                                                                </span>
                                                            )}
                                                        </div>
                                                        <p className="mb-1 h5 text-primary">
                                                            {msg.price || msg.precioPropuesto} créditos
                                                        </p>
                                                        <small className="text-muted d-block mb-2">
                                                            {msg.created_at ? new Date(msg.created_at).toLocaleString() : 'Sin fecha'}
                                                        </small>
                                                        {/* Solo el profesional (no comprador) puede aceptar/rechazar */}
                                                        {!msg.isBuyer && 
                                                         msg.estado !== 'aceptada' && 
                                                         msg.estado !== 'finalizada' && 
                                                         msg.estado !== 'rechazada' && 
                                                         !msg.accepted && 
                                                         !msg.rejected && 
                                                         !msg.aceptado_vendedor && 
                                                         !msg.aceptado_comprador && (
                                                            <div className="d-flex gap-2 mt-2">
                                                                <button
                                                                    className="btn btn-success btn-sm"
                                                                    onClick={() => handleAcceptOffer(msg.id)}
                                                                >
                                                                    <i className="bi bi-check-lg me-1"></i>
                                                                    Aceptar
                                                                </button>
                                                                <button
                                                                    className="btn btn-danger btn-sm"
                                                                    onClick={() => handleRejectOffer(msg.id)}
                                                                >
                                                                    <i className="bi bi-x-lg me-1"></i>
                                                                    Rechazar
                                                                </button>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="card shadow-sm">
                        <div className="card-body">
                            <h5 className="card-title mb-4">Chat</h5>
                            <div className="chat-messages" style={{ height: '400px', overflowY: 'auto' }}>
                                {chatMessages.length === 0 ? (
                                    <p className="text-muted">No hay mensajes aún. ¡Inicia la conversación!</p>
                                ) : (
                                    chatMessages.map((msg, index) => renderMessage(msg))
                                )}
                                <div ref={messagesEndRef} />
                            </div>
                            <form onSubmit={handleSendMessage} className="mt-3">
                                <div className="input-group">
                                    <input
                                        type="text"
                                        className="form-control"
                                        value={message}
                                        onChange={(e) => setMessage(e.target.value)}
                                        placeholder="Escribe un mensaje..."
                                        disabled={sendingMessage}
                                    />
                                    <button
                                        className="btn btn-primary"
                                        type="submit"
                                        disabled={!message.trim() || sendingMessage}
                                    >
                                        {sendingMessage ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Enviando...
                                            </>
                                        ) : (
                                            'Enviar'
                                        )}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ProfessionalNegotiation;