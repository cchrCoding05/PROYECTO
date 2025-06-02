import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { Card, ListGroup, Badge, Spinner, Alert, Button } from 'react-bootstrap';
import '../../styles/Negotiation.css';
import AlertMessage from '../Layout/AlertMessage';

const API_URL = 'http://api.helpex.com:22193/api';

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
            const response = await fetch(`${API_URL}/chat/my-chats`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            console.log('Respuesta del backend (chats):', data);
            
            if (response.status === 401) {
                localStorage.removeItem('token');
                navigate('/login');
                return;
            }

            if (!response.ok) {
                throw new Error(data.message || 'Error al obtener chats');
            }

            if (data.success && Array.isArray(data.data)) {
                console.log('Chats recibidos:', data.data);
                setChats(data.data);
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
        navigate(`/negotiate/professional/${professionalId}`);
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

            const response = await fetch(`${API_URL}/chat/${chatId}/accept-proposal/${proposalId}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
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
                            return {
                                ...chat,
                                status: data.data.estado,
                                isActive: data.data.estado !== 'finalizada' && data.data.estado !== 'rechazada',
                                lastProposal: {
                                    ...chat.lastProposal,
                                    estado: data.data.estado,
                                    aceptado_comprador: data.data.aceptado_comprador,
                                    aceptado_vendedor: data.data.aceptado_vendedor
                                }
                            };
                        }
                        return chat;
                    });
                    return updatedChats;
                });

                // Recargar los chats para asegurar que tenemos los datos más recientes
                await fetchChats();
            }
        } catch (error) {
            console.error('Error al aceptar propuesta:', error);
            setError(error.message || 'Error al aceptar la propuesta');
        }
    };

    const handleRejectProposal = async (chatId, proposalId) => {
        try {
            const token = localStorage.getItem('token');
            if (!token) {
                navigate('/login');
                return;
            }

            const response = await fetch(`${API_URL}/chat/${chatId}/reject-proposal/${proposalId}`, {
                method: 'POST',
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
                throw new Error(data.message || 'Error al rechazar la propuesta');
            }

            if (data.success) {
                // Actualizar el estado local
                setChats(prevChats => prevChats.map(chat => {
                    if (chat.id === chatId) {
                        return {
                            ...chat,
                            status: 'rechazada',
                            isActive: false,
                            lastProposal: {
                                ...chat.lastProposal,
                                estado: 'rechazada'
                            }
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
            <AlertMessage
                message={error}
                type="danger"
                onClose={() => setError(null)}
            />
        );
    }

    console.log('Estado actual de chats en render:', chats);
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
                        {chats.map((chat) => (
                            <ListGroup.Item 
                                key={chat.user.id}
                                className="d-flex justify-content-between align-items-center py-3"
                            >
                                <div className="d-flex align-items-center">
                                    <img 
                                        src={chat.user.photo} 
                                        alt={chat.user.username}
                                        className="rounded-circle me-3"
                                        style={{ width: '50px', height: '50px', objectFit: 'cover' }}
                                    />
                                    <div>
                                        <h5 className="mb-1">{chat.user.username}</h5>
                                        <p className="text-muted mb-0">{chat.user.profession || 'Sin profesión'}</p>
                                        {chat.lastMessage && (
                                            <small className="text-muted d-block">
                                                {chat.lastMessage.content}
                                            </small>
                                        )}
                                    </div>
                                </div>
                                <div className="d-flex align-items-center">
                                    <Button
                                        variant="primary"
                                        size="sm"
                                        onClick={() => handleChatClick(chat.user.id)}
                                    >
                                        <i className="bi bi-chat-dots me-1"></i>
                                        Ver Chat
                                    </Button>
                                </div>
                            </ListGroup.Item>
                        ))}
                    </ListGroup>
                )}
            </Card.Body>
        </Card>
    );
};

export default MyChats; 
