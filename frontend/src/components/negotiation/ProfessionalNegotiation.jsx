import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
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

    useEffect(() => {
        scrollToBottom();
    }, [negotiations]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    const fetchProfessionalData = async () => {
        try {
            const token = localStorage.getItem('token');
            if (!token) {
                navigate('/login');
                return;
            }

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
                console.error('Error en la respuesta:', errorData);
                throw new Error(errorData?.message || 'Error al obtener el chat');
            }

            const chatData = await chatResponse.json();
            console.log('Datos recibidos del chat:', chatData);
            
            if (chatData.success) {
                setProfessional(chatData.data.professional);
                
                // Obtener propuestas de precio específicas del chat
                const priceProposalsResponse = await fetch(`${API_URL}/chat/${id}/price-proposals`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                if (!priceProposalsResponse.ok) {
                    const errorData = await priceProposalsResponse.json().catch(() => null);
                    console.error('Error en la respuesta:', errorData);
                    throw new Error(errorData?.message || 'Error al obtener propuestas de precio');
                }
                
                const priceProposalsData = await priceProposalsResponse.json();
                console.log('Propuestas de precio:', priceProposalsData);

                if (!priceProposalsData.success) {
                    throw new Error(priceProposalsData.message || 'Error al obtener propuestas de precio');
                }

                // Combinar mensajes y propuestas de precio del chat
                const chatMessages = [
                    ...(chatData.data.messages || []).map(msg => ({
                        id: msg.id,
                        message: msg.message,
                        isBuyer: msg.isBuyer,
                        date: msg.created_at,
                        senderName: msg.user_name,
                        precioPropuesto: msg.precioPropuesto,
                        accepted: msg.accepted,
                        rejected: msg.rejected,
                        estado: msg.estado || 'EN_NEGOCIACION',
                        aceptado_comprador: msg.aceptado_comprador,
                        aceptado_vendedor: msg.aceptado_vendedor,
                        isChatProposal: true // Marcar como propuesta de chat
                    })),
                    ...(priceProposalsData.data || []).map(proposal => ({
                        id: proposal.id,
                        message: null,
                        precioPropuesto: proposal.price,
                        isBuyer: proposal.user_id === user.id,
                        date: proposal.created_at,
                        senderName: proposal.user_name,
                        proposedCredits: proposal.price,
                        createdAt: proposal.created_at,
                        accepted: proposal.accepted,
                        rejected: proposal.rejected,
                        estado: proposal.estado || 'EN_NEGOCIACION',
                        aceptado_comprador: proposal.aceptado_comprador,
                        aceptado_vendedor: proposal.aceptado_vendedor,
                        isChatProposal: true // Marcar como propuesta de chat
                    }))
                ]
                .filter((msg, index, self) => 
                    index === self.findIndex((m) => m.id === msg.id)
                )
                .sort((a, b) => new Date(a.date) - new Date(b.date));

                console.log('Mensajes del chat filtrados:', chatMessages);
                setNegotiations(chatMessages);
            } else {
                setError('No se pudo cargar la información del profesional');
            }
        } catch (error) {
            console.error('Error al cargar datos:', error);
            if (error.message.includes('401')) {
                localStorage.removeItem('token');
                navigate('/login');
            } else {
                setError('Error al cargar la información del profesional');
            }
        } finally {
            setLoading(false);
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
                isChatProposal: true // Indicar que es una propuesta de chat
            });
            
            const response = await fetch(`${API_URL}/chat/${id}/propose-price`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    price: parseInt(proposedPrice),
                    isChatProposal: true // Indicar que es una propuesta de chat
                })
            });

            const data = await response.json();
            console.log('Respuesta del servidor:', data);
            
            if (data.success) {
                setNegotiationSuccess(true);
                setProposedPrice('');
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

            console.log('Respuesta del servidor:', {
                status: response.status,
                statusText: response.statusText
            });

            const data = await response.json();
            console.log('Datos de la respuesta:', data);

            if (data.success) {
                setShowRatingModal(false);
                setComment('');
                setRating(5);
                setActionMessage({ type: 'success', text: 'Valoración enviada con éxito' });
                fetchProfessionalData();
            } else {
                // Mensaje específico para cuando ya existe una valoración
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

    const handleSendMessage = async () => {
        if (!message.trim()) return;

        try {
            const response = await fetch(`${API_URL}/chat/${id}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify({ 
                    message: message.trim()
                })
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => null);
                throw new Error(errorData?.message || 'Error al enviar mensaje');
            }

            const data = await response.json();
            if (data.success) {
                setMessage('');
                fetchProfessionalData();
            } else {
                throw new Error(data.message || 'Error al enviar mensaje');
            }
        } catch (error) {
            console.error('Error al enviar mensaje:', error);
            setError('Error al enviar el mensaje');
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
                // Actualizar el estado de la propuesta en el estado local usando los datos del backend
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
                // Actualizar el estado de la propuesta en el estado local
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

    const isNegotiationActive = (negotiation) => {
        if (!negotiation) return false;
        return !negotiation.accepted && !negotiation.rejected;
    };

    const canAcceptReject = (negotiation) => {
        if (!negotiation || !user) return false;
        return negotiation.recipientId === user.id && isNegotiationActive(negotiation);
    };

    const hasActiveNegotiation = () => {
        return false;
    };

    const canProposePrice = () => {
        return true;
    };

    const renderMessage = (msg) => {
        const isCurrentUser = msg.isBuyer === (user.role === 'ROLE_BUYER');
        const isPriceProposal = msg.precioPropuesto !== null && msg.precioPropuesto !== undefined;
        const isActive = msg.estado === 'EN_NEGOCIACION' && !msg.accepted && !msg.rejected;
        const isAccepted = msg.estado === 'aceptada' || msg.estado === 'finalizada' || msg.accepted;
        const isRejected = msg.estado === 'rechazada' || msg.rejected;

        return (
            <div key={msg.id} className={`message ${isCurrentUser ? 'sent' : 'received'}`}>
                <div className="message-content">
                    {msg.message && <p>{msg.message}</p>}
                    {isPriceProposal && (
                        <div className="price-proposal">
                            <p className="price">Precio propuesto: {msg.precioPropuesto} créditos</p>
                            {!isCurrentUser && isActive && (
                                <div className="price-actions">
                                    <button 
                                        onClick={() => handleAcceptOffer(msg.id)}
                                        className="accept-btn"
                                    >
                                        Aceptar
                                    </button>
                                    <button 
                                        onClick={() => handleRejectOffer(msg.id)}
                                        className="reject-btn"
                                    >
                                        Rechazar
                                    </button>
                                </div>
                            )}
                            {isAccepted && <p className="status accepted">Precio aceptado</p>}
                            {isRejected && <p className="status rejected">Precio rechazado</p>}
                            {isActive && <p className="status pending">Precio pendiente</p>}
                        </div>
                    )}
                    <span className="message-time">
                        {new Date(msg.date).toLocaleString()}
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
                            <div className="list-group">
                                {negotiations
                                    .filter(msg => msg.precioPropuesto !== null && msg.precioPropuesto !== undefined)
                                    .sort((a, b) => new Date(b.date) - new Date(a.date))
                                    .map((msg, index) => (
                                        <div 
                                            key={index} 
                                            className="list-group-item"
                                        >
                                            <div className="d-flex justify-content-between align-items-start">
                                                <div className="flex-grow-1">
                                                    <div className="d-flex align-items-center mb-2">
                                                        <h6 className="mb-0 me-2">
                                                            {msg.senderName}
                                                        </h6>
                                                        {(msg.estado === 'aceptada' || msg.estado === 'finalizada' || msg.accepted) && (
                                                            <span className="badge bg-success ms-2">
                                                                Aceptada
                                                            </span>
                                                        )}
                                                        {(msg.estado === 'rechazada' || msg.rejected) && (
                                                            <span className="badge bg-danger ms-2">
                                                                Rechazada
                                                            </span>
                                                        )}
                                                        {msg.estado === 'EN_NEGOCIACION' && !msg.accepted && !msg.rejected && (
                                                            <span className="badge bg-primary ms-2">
                                                                Activa
                                                            </span>
                                                        )}
                                                    </div>
                                                    <p className="mb-1 h5 text-primary">
                                                        {msg.precioPropuesto} créditos
                                                    </p>
                                                    <small className="text-muted d-block mb-2">
                                                        {new Date(msg.date).toLocaleString()}
                                                    </small>
                                                    {!msg.isBuyer && msg.estado !== 'aceptada' && msg.estado !== 'finalizada' && msg.estado !== 'rechazada' && !msg.accepted && !msg.rejected && (
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
                        </div>
                    </div>

                    <div className="card shadow-sm">
                        <div className="card-body">
                            <h5 className="card-title mb-4">Chat</h5>
                            <div className="chat-messages" style={{ height: '400px', overflowY: 'auto' }}>
                                {negotiations
                                    .filter(msg => msg.message && !msg.isRating)
                                    .map((msg, index) => renderMessage(msg))}
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
                                    />
                                    <button 
                                        className="btn btn-primary" 
                                        type="submit"
                                        disabled={!message.trim()}
                                    >
                                        Enviar
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