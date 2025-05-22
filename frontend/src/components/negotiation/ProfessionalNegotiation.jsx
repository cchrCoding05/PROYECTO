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

    useEffect(() => {
        const token = localStorage.getItem('token');
        if (!user || !token) {
            navigate('/login');
            return;
        }
        fetchProfessionalData();
        const interval = setInterval(fetchProfessionalData, 15000);
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

            const response = await fetch(`${API_URL}/professional-chat/${id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (response.status === 401) {
                localStorage.removeItem('token');
                navigate('/login');
                return;
            }

            const data = await response.json();
            if (data.success) {
                setProfessional(data.data.professional);
                const chatMessages = data.data.valoraciones?.filter(msg => !msg.isRating) || [];
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

    const handleRateProfessional = async () => {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${API_URL}/professionals/${id}/rate`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    puntuacion: rating,
                    comentario: comment
                })
            });
            const data = await response.json();
            if (data.success) {
                setShowRatingModal(false);
                setComment('');
                fetchProfessionalData();
            } else {
                setError(data.message || 'Error al enviar la valoración');
            }
        } catch (error) {
            setError('Error al enviar la valoración');
        }
    };

    const handleSendMessage = async () => {
        if (!message.trim()) return;

        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${API_URL}/professional-chat/${id}/start`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: message.trim()
                })
            });
            const data = await response.json();
            if (data.success) {
                setMessage('');
                fetchProfessionalData();
            } else {
                setError(data.error || 'Error al enviar el mensaje');
            }
        } catch (error) {
            setError('Error al enviar el mensaje');
        }
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
        <div className="negotiation-container">
            {error && (
                <AlertMessage
                    message={error}
                    type="danger"
                    onClose={() => setError(null)}
                />
            )}

            {/* Panel de información del profesional */}
            <div className="product-section">
                <div className="product-info">
                    <div className="product-image-wrapper">
                        {professional?.photo ? (
                            <img
                                src={professional.photo}
                                alt={professional.name}
                                className="product-detail-image"
                            />
                        ) : (
                            <div
                                className="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold"
                                style={{ width: '100%', height: '100%' }}
                            >
                                {professional?.name?.charAt(0).toUpperCase()}
                            </div>
                        )}
                    </div>
                    <h2 className="product-title">{professional?.name}</h2>
                    <div className="product-name">{professional?.profession}</div>
                    <div className="current-price">
                        <div className="price-label">Valoración</div>
                        <div className="price-value">
                            {[1, 2, 3, 4, 5].map((star) => (
                                <span
                                    key={star}
                                    className={`star ${star <= (professional?.rating || 0) ? 'filled' : ''}`}
                                >
                                    ★
                                </span>
                            ))}
                            <small className="ms-2">
                                ({professional?.reviews_count || 0} valoraciones)
                            </small>
                        </div>
                    </div>
                    <button
                        className="btn btn-primary w-100 mt-3"
                        onClick={() => setShowRatingModal(true)}
                    >
                        Valorar profesional
                    </button>
                </div>
            </div>

            {/* Panel de chat */}
            <div className="chat-section">
                <div className="chat-header">
                    <div className="seller-info">
                        Chat con {professional?.name}
                    </div>
                </div>
                <div className="chat-messages">
                    {negotiations
                        .filter(negotiation => negotiation.message && !negotiation.isRating)
                        .map((negotiation) => (
                        <div
                            key={negotiation.id}
                            className={`message ${negotiation.isActive ? 'active' : ''}`}
                        >
                            <div className="message-bubble">
                                <div className="price-proposal">
                                    {negotiation.isBuyer ? 'Tú' : professional?.name}: {negotiation.message}
                                </div>
                                <small className="message-time">
                                    {negotiation.date ? new Date(negotiation.date).toLocaleString('es-ES', {
                                        year: 'numeric',
                                        month: '2-digit',
                                        day: '2-digit',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) : ''}
                                </small>
                            </div>
                        </div>
                    ))}
                    <div ref={messagesEndRef} />
                </div>
                <div className="chat-input">
                    <div className="input-group">
                        <input
                            type="text"
                            className="form-control"
                            placeholder="Escribe un mensaje..."
                            value={message}
                            onChange={(e) => setMessage(e.target.value)}
                            onKeyPress={(e) => e.key === 'Enter' && handleSendMessage()}
                        />
                        <button
                            className="btn btn-primary"
                            onClick={handleSendMessage}
                            disabled={!message.trim()}
                        >
                            Enviar
                        </button>
                    </div>
                </div>
            </div>

            {/* Modal de valoración */}
            {showRatingModal && (
                <div className="modal fade show" style={{ display: 'block' }}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">Valorar profesional</h5>
                                <button
                                    type="button"
                                    className="btn-close"
                                    onClick={() => setShowRatingModal(false)}
                                ></button>
                            </div>
                            <div className="modal-body">
                                <div className="mb-3">
                                    <label className="form-label">Valoración</label>
                                    <select
                                        className="form-select"
                                        value={rating}
                                        onChange={(e) => setRating(Number(e.target.value))}
                                    >
                                        {[1, 2, 3, 4, 5].map((value) => (
                                            <option key={value} value={value}>
                                                {value} {value === 1 ? 'estrella' : 'estrellas'}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="mb-3">
                                    <label className="form-label">Comentario</label>
                                    <textarea
                                        className="form-control"
                                        value={comment}
                                        onChange={(e) => setComment(e.target.value)}
                                        rows="3"
                                        placeholder="Escribe tu comentario aquí..."
                                        required
                                    ></textarea>
                                </div>
                            </div>
                            <div className="modal-footer">
                                <button
                                    type="button"
                                    className="btn btn-secondary"
                                    onClick={() => setShowRatingModal(false)}
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-primary"
                                    onClick={handleRateProfessional}
                                    disabled={!comment.trim()}
                                >
                                    Enviar valoración
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default ProfessionalNegotiation; 