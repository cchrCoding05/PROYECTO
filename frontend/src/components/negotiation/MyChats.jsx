import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { Card, ListGroup, Badge, Spinner, Alert, Button } from 'react-bootstrap';
import '../../styles/Negotiation.css';

const API_URL = 'http://localhost:8000/api';

const MyChats = () => {
    const [chats, setChats] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate();
    const { isAuthenticated, currentUser } = useAuth();

    useEffect(() => {
        if (!isAuthenticated || !currentUser) {
            navigate('/login');
            return;
        }
        fetchChats();
    }, [isAuthenticated, currentUser, navigate]);

    const fetchChats = async () => {
        try {
            const token = localStorage.getItem('token');
            if (!token) {
                navigate('/login');
                return;
            }

            setLoading(true);
            setError(null);

            // Obtener chats
            const response = await fetch(`${API_URL}/professionals/chats`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (response.status === 401) {
                localStorage.removeItem('token');
                navigate('/login');
                return;
            }

            if (!response.ok) {
                throw new Error(data.message || 'Error al obtener chats');
            }

            if (data.success && Array.isArray(data.data)) {
                console.table(data.data);

                // Para cada chat, obtener sus propuestas de precio
                const chatsWithProposals = await Promise.all(data.data.map(async (chat) => {
                    try {
                        const proposalsResponse = await fetch(`${API_URL}/professionals/${chat.id}/chat/price-proposals`, {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });

                        const proposalsData = await proposalsResponse.json();
                        
                        if (proposalsData.success && Array.isArray(proposalsData.data)) {
                            // Obtener la última propuesta
                            const lastProposal = proposalsData.data
                                .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0];

                            // Log detallado de la propuesta
                            console.log('%c=== DETALLES DE PROPUESTA ===', 'background: #2196F3; color: white; font-size: 16px; padding: 5px;');
                            console.log('Chat ID:', chat.id);
                            console.table({
                                'ID': lastProposal?.id,
                                'Precio': lastProposal?.price,
                                'Estado': lastProposal?.estado,
                                'Aceptado Comprador': lastProposal?.aceptado_comprador,
                                'Aceptado Vendedor': lastProposal?.aceptado_vendedor,
                                'Fecha': lastProposal?.created_at
                            });
                            console.log('%c=== FIN DETALLES ===', 'background: #2196F3; color: white; font-size: 16px; padding: 5px;');

                            return {
                                ...chat,
                                lastMessage: lastProposal ? {
                                    id: lastProposal.id,
                                    price: lastProposal.price,
                                    created_at: lastProposal.created_at,
                                    estado: lastProposal.estado || 'EN_NEGOCIACION',
                                    aceptado_comprador: lastProposal.aceptado_comprador || false,
                                    aceptado_vendedor: lastProposal.aceptado_vendedor || false
                                } : null
                            };
                        }
                        return chat;
                    } catch (error) {
                        console.error('Error al obtener propuestas para chat', chat.id, ':', error);
                        return chat;
                    }
                }));

                // Transformar los datos al formato esperado por el componente
                const transformedChats = chatsWithProposals.map(chat => {
                    // Determinar si el usuario actual es el vendedor
                    const isSeller = chat.professional.id === currentUser.id;
                    
                    // Determinar el estado de la propuesta
                    let status = 'EN_NEGOCIACION';
                    let isActive = true;

                    if (chat.lastMessage) {
                        // Si ambos han aceptado, marcar como finalizada
                        if (chat.lastMessage.aceptado_comprador && chat.lastMessage.aceptado_vendedor) {
                            status = 'finalizada';
                            isActive = false;
                        }
                        // Si solo uno ha aceptado, marcar como aceptada
                        else if (chat.lastMessage.aceptado_comprador || chat.lastMessage.aceptado_vendedor) {
                            status = 'aceptada';
                            isActive = true;
                        }
                        // Si está rechazada
                        else if (chat.lastMessage.estado === 'rechazada') {
                            status = 'rechazada';
                            isActive = false;
                        }
                        // Si está finalizada por otro motivo
                        else if (chat.lastMessage.estado === 'finalizada') {
                            status = 'finalizada';
                            isActive = false;
                        }
                    }
                    
                    return {
                        id: chat.id,
                        isSeller: isSeller,
                        buyer: isSeller ? chat.professional : currentUser,
                        seller: isSeller ? currentUser : chat.professional,
                        proposedCredits: chat.lastMessage?.price || 0,
                        date: chat.lastMessage?.created_at || new Date().toISOString(),
                        status: status,
                        isActive: isActive,
                        lastMessage: chat.lastMessage
                    };
                });

                // Log detallado de estados de chats
                console.log('%c=== ESTADOS DE CHATS ===', 'background: #222; color: #bada55; font-size: 16px; padding: 5px;');
                transformedChats.forEach((chat, index) => {
                    console.log(`%cChat ${index + 1}:`, 'color: #4CAF50; font-weight: bold;');
                    console.table({
                        'ID': chat.id,
                        'Vendedor': chat.seller.name,
                        'Comprador': chat.buyer.name,
                        'Precio': chat.proposedCredits,
                        'Estado': chat.status,
                        'Activo': chat.isActive,
                        'Fecha': chat.date,
                        'Estado Original': chat.lastMessage?.estado,
                        'Aceptado Comprador': chat.lastMessage?.aceptado_comprador,
                        'Aceptado Vendedor': chat.lastMessage?.aceptado_vendedor
                    });
                });
                console.log('%c=== FIN ESTADOS DE CHATS ===', 'background: #222; color: #bada55; font-size: 16px; padding: 5px;');

                setChats(transformedChats);
            } else {
                setChats([]);
            }
        } catch (error) {
            console.error('Error al obtener chats:', error);
            setError(error.message || 'Error al cargar los chats');
        } finally {
            setLoading(false);
        }
    };

    const handleChatClick = (professionalId) => {
        if (!professionalId) {
            console.error('ID de profesional inválido');
            return;
        }
        navigate(`/professional-chat/${professionalId}`);
    };

    const handleAcceptProposal = async (chatId, proposalId) => {
        try {
            console.log('=== INICIO ACEPTACIÓN DE PROPUESTA ===');
            console.log('Datos de la propuesta:', { 
                chatId, 
                proposalId,
                chat: chats.find(c => c.id === chatId)
            });

            const token = localStorage.getItem('token');
            if (!token) {
                console.error('No hay token de autenticación');
                navigate('/login');
                return;
            }

            console.log('Llamando a la API para aceptar propuesta:', {
                url: `${API_URL}/professionals/${chatId}/chat/accept-proposal/${proposalId}`,
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const response = await fetch(`${API_URL}/professionals/${chatId}/chat/accept-proposal/${proposalId}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            console.log('Respuesta del servidor:', {
                status: response.status,
                statusText: response.statusText
            });

            const data = await response.json();
            console.log('Datos de la respuesta:', data);

            if (response.status === 401) {
                console.error('Token expirado o inválido');
                localStorage.removeItem('token');
                navigate('/login');
                return;
            }

            if (!response.ok) {
                throw new Error(data.message || 'Error al aceptar la propuesta');
            }

            if (data.success) {
                console.log('Propuesta aceptada exitosamente:', data);
                // Actualizar el estado local
                setChats(prevChats => {
                    const updatedChats = prevChats.map(chat => {
                        if (chat.id === chatId) {
                            console.log('Actualizando chat en el estado:', {
                                chatId,
                                estadoAnterior: chat.status,
                                estadoNuevo: data.data.estado
                            });
                            return {
                                ...chat,
                                status: data.data.estado,
                                isActive: data.data.estado !== 'finalizada' && data.data.estado !== 'rechazada',
                                lastMessage: {
                                    ...chat.lastMessage,
                                    estado: data.data.estado,
                                    aceptado_comprador: data.data.aceptado_comprador,
                                    aceptado_vendedor: data.data.aceptado_vendedor
                                }
                            };
                        }
                        return chat;
                    });
                    console.log('Estado de chats actualizado:', updatedChats);
                    return updatedChats;
                });

                // Recargar los chats para asegurar que tenemos los datos más recientes
                await fetchChats();
            }
            console.log('=== FIN ACEPTACIÓN DE PROPUESTA ===');
        } catch (error) {
            console.error('Error al aceptar propuesta:', error);
            setError(error.message || 'Error al aceptar la propuesta');
        }
    };

    const handleRejectProposal = async (chatId, proposalId) => {
        try {
            console.log('Iniciando rechazo de propuesta:', { proposalId });
            const token = localStorage.getItem('token');
            if (!token) {
                navigate('/login');
                return;
            }

            console.log('Llamando a la API con:', {
                url: `${API_URL}/professionals/${chatId}/chat/reject-proposal/${proposalId}`,
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const response = await fetch(`${API_URL}/professionals/${chatId}/chat/reject-proposal/${proposalId}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            console.log('Respuesta recibida:', response.status, response.statusText);
            const data = await response.json();
            console.log('Datos de la respuesta:', data);

            if (response.status === 401) {
                localStorage.removeItem('token');
                navigate('/login');
                return;
            }

            if (!response.ok) {
                throw new Error(data.message || 'Error al rechazar la propuesta');
            }

            if (data.success) {
                console.log('Propuesta rechazada exitosamente:', data);
                // Actualizar el estado local
                setChats(prevChats => prevChats.map(chat => {
                    if (chat.id === chatId) {
                        console.log('Actualizando chat:', chat);
                        return {
                            ...chat,
                            status: 'rejected',
                            isActive: false
                        };
                    }
                    return chat;
                }));
            }
        } catch (error) {
            console.error('Error al rechazar propuesta:', error);
            setError(error.message || 'Error al rechazar la propuesta');
        }
    };

    if (loading) {
        return (
            <div className="text-center py-4">
                <Spinner animation="border" role="status">
                    <span className="visually-hidden">Cargando...</span>
                </Spinner>
            </div>
        );
    }

    if (error) {
        return (
            <Alert variant="danger" className="m-3">
                {error}
            </Alert>
        );
    }

    return (
        <Card className="shadow-sm">
            <Card.Header className="bg-primary text-white">
                <h4 className="mb-0">Mis Chats</h4>
            </Card.Header>
            <Card.Body>
                {chats.length === 0 ? (
                    <div className="text-center py-4">
                        <i className="bi bi-chat-square-text display-4 text-muted mb-3"></i>
                        <p className="text-muted">No tienes chats activos</p>
                    </div>
                ) : (
                    <ListGroup variant="flush">
                        {chats.map((chat) => {
                            // Determinar quién es el otro usuario (no el actual)
                            const otherUser = chat.isSeller ? chat.buyer : chat.seller;
                            
                            return (
                                <ListGroup.Item 
                                    key={chat.id}
                                    className="d-flex justify-content-between align-items-center py-3"
                                >
                                    <div className="d-flex align-items-center">
                                        <img 
                                            src={otherUser.photo || '/default-avatar.png'} 
                                            alt={otherUser.name || 'Usuario'}
                                            className="rounded-circle me-3"
                                            style={{ width: '50px', height: '50px', objectFit: 'cover' }}
                                        />
                                        <div>
                                            <h5 className="mb-1">{otherUser.name || 'Usuario'}</h5>
                                            <p className="text-muted mb-0">{otherUser.profession || 'Sin profesión'}</p>
                                            <small className="text-muted">
                                                {chat.proposedCredits ? `Propuesta de ${chat.proposedCredits} créditos` : 'Sin propuesta'}
                                            </small>
                                            {chat.status === 'finalizada' && (
                                                <Badge bg="success" className="ms-2">Finalizada</Badge>
                                            )}
                                            {chat.status === 'aceptada' && (
                                                <Badge bg="primary" className="ms-2">Aceptada</Badge>
                                            )}
                                            {chat.status === 'rechazada' && (
                                                <Badge bg="danger" className="ms-2">Rechazada</Badge>
                                            )}
                                            {chat.status === 'EN_NEGOCIACION' && (
                                                <Badge bg="warning" text="dark" className="ms-2">En negociación</Badge>
                                            )}
                                        </div>
                                    </div>
                                    <div className="d-flex align-items-center">
                                        {chat.lastMessage && chat.lastMessage.id && chat.isActive && (
                                            <>
                                                <Button 
                                                    variant="success" 
                                                    size="sm" 
                                                    className="me-2"
                                                    onClick={() => handleAcceptProposal(chat.id, chat.lastMessage.id)}
                                                >
                                                    <i className="bi bi-check-lg me-1"></i>
                                                    Aceptar
                                                </Button>
                                                <Button 
                                                    variant="danger" 
                                                    size="sm" 
                                                    className="me-2"
                                                    onClick={() => handleRejectProposal(chat.id, chat.lastMessage.id)}
                                                >
                                                    <i className="bi bi-x-lg me-1"></i>
                                                    Rechazar
                                                </Button>
                                            </>
                                        )}
                                        <Badge bg="light" text="dark">
                                            {new Date(chat.date).toLocaleString()}
                                        </Badge>
                                    </div>
                                </ListGroup.Item>
                            );
                        })}
                    </ListGroup>
                )}
            </Card.Body>
        </Card>
    );
};

export default MyChats; 