import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { serviceNegotiationService } from '../../services/api';
import { Card, Form, Button, Alert, Spinner } from 'react-bootstrap';
import { FaPaperPlane, FaCheck, FaTimes } from 'react-icons/fa';

const ServiceNegotiation = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { user, token } = useAuth();
    const [negociacion, setNegociacion] = useState(null);
    const [mensaje, setMensaje] = useState('');
    const [nuevoPrecio, setNuevoPrecio] = useState('');
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(true);
    const [sending, setSending] = useState(false);
    const [actionLoading, setActionLoading] = useState(false);

    const initializeNegotiation = useCallback(async () => {
        if (!token) {
            navigate('/login', { state: { from: `/negotiation-service/${id}` } });
            return;
        }

        try {
            setLoading(true);
            setError(null);
            const response = await serviceNegotiationService.obtenerNegociacion(id);
            
            if (response.success && response.data) {
                setNegociacion(response.data);
                setNuevoPrecio(response.data.creditos_propuestos?.toString() || '');
            } else {
                throw new Error(response.message || 'Error al cargar la negociación');
            }
        } catch (error) {
            console.error('Error al cargar negociación:', error);
            setError(error.message || 'Error al cargar la negociación');
            
            if (error.response?.status === 401) {
                navigate('/login', { state: { from: `/negotiation-service/${id}` } });
                return;
            }
        } finally {
            setLoading(false);
        }
    }, [id, token, navigate]);

    useEffect(() => {
        initializeNegotiation();
    }, [initializeNegotiation]);

    const handleSendMessage = async (e) => {
        e.preventDefault();
        if (!mensaje.trim() || !token) return;

        setSending(true);
        setError(null);
        try {
            const response = await serviceNegotiationService.enviarMensaje(id, mensaje, user.id);
            if (response.success && response.data) {
                setMensaje('');
                setNegociacion(prev => ({
                    ...prev,
                    mensajes: [...(prev?.mensajes || []), response.data]
                }));
            } else {
                throw new Error(response.message || 'Error al enviar el mensaje');
            }
        } catch (err) {
            console.error('Error al enviar mensaje:', err);
            setError(err.message || 'Error al enviar el mensaje');
        } finally {
            setSending(false);
        }
    };

    const handleProposePrice = async (e) => {
        e.preventDefault();
        if (!token) {
            navigate('/login', { state: { from: `/negotiation-service/${id}` } });
            return;
        }

        const creditos = parseInt(nuevoPrecio);
        if (isNaN(creditos) || creditos <= 0) {
            setError('Por favor, introduce una cantidad válida de créditos');
            return;
        }

        setActionLoading(true);
        setError(null);
        try {
            const response = await serviceNegotiationService.iniciarNegociacion(
                id,
                creditos
            );
            if (response.success && response.data) {
                setNegociacion(prev => ({
                    ...prev,
                    creditos_propuestos: creditos,
                    estado: 'EN_NEGOCIACION'
                }));
            } else {
                throw new Error(response.message || 'Error al proponer precio');
            }
        } catch (err) {
            console.error('Error al proponer precio:', err);
            setError(err.message || 'Error al proponer precio');
        } finally {
            setActionLoading(false);
        }
    };

    const handleAction = async (action) => {
        if (!token) {
            navigate('/login', { state: { from: `/negotiation-service/${id}` } });
            return;
        }

        setActionLoading(true);
        setError(null);
        try {
            let response;
            switch (action) {
                case 'aceptar':
                    response = await serviceNegotiationService.aceptarNegociacion(id);
                    break;
                case 'rechazar':
                    response = await serviceNegotiationService.rechazarNegociacion(id);
                    break;
                case 'completar':
                    response = await serviceNegotiationService.completarNegociacion(id);
                    if (response.success && response.data?.redirect_url) {
                        console.log('Redirigiendo a:', response.data.redirect_url);
                        navigate(response.data.redirect_url);
                        return;
                    }
                    break;
                default:
                    throw new Error('Acción no válida');
            }

            if (response.success && response.data) {
                setNegociacion(prev => ({
                    ...prev,
                    ...response.data
                }));
            } else {
                throw new Error(response.message || `Error al ${action} la negociación`);
            }
        } catch (err) {
            console.error(`Error al ${action} negociación:`, err);
            setError(err.message || `Error al ${action} la negociación`);
        } finally {
            setActionLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="d-flex justify-content-center align-items-center" style={{ height: '100vh' }}>
                <Spinner animation="border" role="status" variant="primary">
                    <span className="visually-hidden">Cargando...</span>
                </Spinner>
            </div>
        );
    }

    if (!negociacion) {
        return (
            <Alert variant="danger" className="m-3">
                {error || 'No se pudo cargar la negociación'}
            </Alert>
        );
    }

    const isProfesional = user?.id === negociacion.profesional?.id;
    const canProposePrice = !isProfesional && negociacion.estado === 'EN_NEGOCIACION';
    const canAcceptReject = isProfesional && negociacion.estado === 'EN_NEGOCIACION';
    const canComplete = !isProfesional && negociacion.estado === 'ACEPTADA';

    return (
        <div className="container-fluid py-4">
            {error && (
                <Alert variant="danger" className="mb-3" onClose={() => setError(null)} dismissible>
                    {error}
                </Alert>
            )}

            <div className="row">
                {/* Columna Izquierda */}
                <div className="col-md-4">
                    {/* Tarjeta del Profesional */}
                    <Card className="mb-4">
                        <Card.Body>
                            <div className="d-flex align-items-center mb-3">
                                {negociacion.profesional?.foto_perfil ? (
                                    <img
                                        src={negociacion.profesional.foto_perfil}
                                        alt={negociacion.profesional.nombre}
                                        className="rounded-circle me-3"
                                        style={{ width: '60px', height: '60px', objectFit: 'cover' }}
                                    />
                                ) : (
                                    <div
                                        className="rounded-circle d-flex align-items-center justify-content-center me-3 bg-primary bg-opacity-10 text-primary"
                                        style={{ width: '60px', height: '60px' }}
                                    >
                                        {negociacion.profesional?.nombre?.charAt(0).toUpperCase()}
                                    </div>
                                )}
                                <div>
                                    <h5 className="mb-1">{negociacion.profesional?.nombre}</h5>
                                    <p className="text-muted mb-0">
                                        Estado: {negociacion.estado}
                                    </p>
                                </div>
                            </div>
                        </Card.Body>
                    </Card>

                    {/* Formulario de Precio */}
                    {canProposePrice && (
                        <Card>
                            <Card.Body>
                                <Form onSubmit={handleProposePrice}>
                                    <Form.Group className="mb-3">
                                        <Form.Label>Propuesta de Precio (créditos)</Form.Label>
                                        <Form.Control
                                            type="number"
                                            value={nuevoPrecio}
                                            onChange={(e) => setNuevoPrecio(e.target.value)}
                                            min="1"
                                            required
                                            disabled={actionLoading}
                                        />
                                        <Form.Text className="text-muted">
                                            Último precio propuesto: {negociacion.creditos_propuestos} créditos
                                        </Form.Text>
                                    </Form.Group>
                                    <Button
                                        type="submit"
                                        variant="primary"
                                        className="w-100"
                                        disabled={actionLoading}
                                    >
                                        {actionLoading ? (
                                            <>
                                                <Spinner
                                                    as="span"
                                                    animation="border"
                                                    size="sm"
                                                    role="status"
                                                    aria-hidden="true"
                                                    className="me-2"
                                                />
                                                Enviando...
                                            </>
                                        ) : (
                                            'Enviar Propuesta'
                                        )}
                                    </Button>
                                </Form>
                            </Card.Body>
                        </Card>
                    )}

                    {/* Botones de Acción */}
                    {canAcceptReject && (
                        <div className="d-grid gap-2 mt-3">
                            <Button
                                variant="success"
                                onClick={() => handleAction('aceptar')}
                                disabled={actionLoading}
                            >
                                {actionLoading ? (
                                    <>
                                        <Spinner
                                            as="span"
                                            animation="border"
                                            size="sm"
                                            role="status"
                                            aria-hidden="true"
                                            className="me-2"
                                        />
                                        Procesando...
                                    </>
                                ) : (
                                    <>
                                        <FaCheck className="me-2" />
                                        Aceptar Propuesta
                                    </>
                                )}
                            </Button>
                            <Button
                                variant="danger"
                                onClick={() => handleAction('rechazar')}
                                disabled={actionLoading}
                            >
                                {actionLoading ? (
                                    <>
                                        <Spinner
                                            as="span"
                                            animation="border"
                                            size="sm"
                                            role="status"
                                            aria-hidden="true"
                                            className="me-2"
                                        />
                                        Procesando...
                                    </>
                                ) : (
                                    <>
                                        <FaTimes className="me-2" />
                                        Rechazar Propuesta
                                    </>
                                )}
                            </Button>
                        </div>
                    )}

                    {canComplete && (
                        <Button
                            variant="primary"
                            className="w-100 mt-3"
                            onClick={() => handleAction('completar')}
                            disabled={actionLoading}
                        >
                            {actionLoading ? (
                                <>
                                    <Spinner
                                        as="span"
                                        animation="border"
                                        size="sm"
                                        role="status"
                                        aria-hidden="true"
                                        className="me-2"
                                    />
                                    Procesando...
                                </>
                            ) : (
                                'Completar Servicio'
                            )}
                        </Button>
                    )}
                </div>

                {/* Columna Derecha - Chat */}
                <div className="col-md-8">
                    <Card className="h-100">
                        <Card.Header>
                            <h5 className="mb-0">Chat de Negociación</h5>
                        </Card.Header>
                        <Card.Body className="d-flex flex-column" style={{ height: '600px' }}>
                            {/* Mensajes */}
                            <div className="flex-grow-1 overflow-auto mb-3">
                                {negociacion.mensajes?.length > 0 ? (
                                    negociacion.mensajes.map((msg) => (
                                        <div
                                            key={msg.id}
                                            className={`d-flex mb-3 ${
                                                msg.emisor.id === user?.id
                                                    ? 'justify-content-end'
                                                    : 'justify-content-start'
                                            }`}
                                        >
                                            <div
                                                className={`p-3 rounded ${
                                                    msg.emisor.id === user?.id
                                                        ? 'bg-primary text-white'
                                                        : 'bg-light'
                                                }`}
                                                style={{ maxWidth: '70%' }}
                                            >
                                                <small className="d-block text-muted mb-1">
                                                    {msg.emisor.nombre}
                                                </small>
                                                <div>{msg.contenido}</div>
                                                <small className="d-block text-muted mt-1">
                                                    {new Date(msg.fecha_envio).toLocaleString()}
                                                </small>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center text-muted my-5">
                                        No hay mensajes aún. ¡Inicia la conversación!
                                    </div>
                                )}
                            </div>

                            {/* Formulario de Mensaje */}
                            <Form onSubmit={handleSendMessage} className="mt-auto">
                                <div className="input-group">
                                    <Form.Control
                                        type="text"
                                        value={mensaje}
                                        onChange={(e) => setMensaje(e.target.value)}
                                        placeholder="Escribe un mensaje..."
                                        disabled={sending || !token}
                                    />
                                    <Button
                                        type="submit"
                                        variant="primary"
                                        disabled={sending || !mensaje.trim() || !token}
                                    >
                                        {sending ? (
                                            <Spinner
                                                as="span"
                                                animation="border"
                                                size="sm"
                                                role="status"
                                                aria-hidden="true"
                                            />
                                        ) : (
                                            <FaPaperPlane />
                                        )}
                                    </Button>
                                </div>
                            </Form>
                        </Card.Body>
                    </Card>
                </div>
            </div>
        </div>
    );
};

export default ServiceNegotiation; 