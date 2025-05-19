import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import axios from 'axios';
import {
    Container,
    Grid,
    Paper,
    Typography,
    TextField,
    Button,
    Box,
    Alert,
    CircularProgress,
    Divider,
    List,
    ListItem,
    ListItemText,
    IconButton,
    Chip,
    Rating,
    Avatar,
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions
} from '@mui/material';

const API_URL = import.meta.env.VITE_API_URL;

const ProfessionalNegotiation = ({ mode = 'chat' }) => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { token, user } = useAuth();
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [professional, setProfessional] = useState(null);
    const [negociacion, setNegociacion] = useState(null);
    const [mensaje, setMensaje] = useState('');
    const [sending, setSending] = useState(false);
    const [actionLoading, setActionLoading] = useState(false);
    const [creditosPropuestos, setCreditosPropuestos] = useState('');
    const [mensajeInicial, setMensajeInicial] = useState('');
    const [puntuacion, setPuntuacion] = useState(0);
    const [comentario, setComentario] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const messagesEndRef = useRef(null);

    // Modos de visualización
    const isInitiateMode = mode === 'initiate';
    const isChatMode = mode === 'chat';
    const isRateMode = mode === 'rate';

    useEffect(() => {
        console.log('ProfessionalNegotiation useEffect:', {
            mode,
            isInitiateMode,
            hasToken: !!token,
            userId: user?.id,
            professionalId: id
        });

        // Solo redirigir al login si no hay token
        if (!token) {
            console.log('No hay token, redirigiendo a login');
            navigate('/login', { state: { from: `/negotiation-service/${id}` } });
            return;
        }

        // Si hay token, proceder con la carga de datos
        if (token) {
            console.log('Token presente, procediendo con carga de datos');
            const initializeNegotiation = async () => {
                try {
                    console.log('Intentando cargar negociación:', {
                        url: `${API_URL}/api/negociacion-servicio/${id}`,
                        headers: {
                            'Authorization': `Bearer ${token.substring(0, 10)}...`,
                            'Content-Type': 'application/json'
                        }
                    });

                    const response = await axios.get(`${API_URL}/api/negociacion-servicio/${id}`, {
                        headers: { 
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        }
                    });

                    console.log('Respuesta del servidor:', response.data);

                    if (response.data.success) {
                        console.log('Negociación encontrada:', response.data.data);
                        setNegociacion(response.data.data);
                    } else {
                        throw new Error(response.data.message || 'Error al cargar la negociación');
                    }
                } catch (error) {
                    console.error('Error detallado al inicializar negociación:', {
                        message: error.message,
                        status: error.response?.status,
                        statusText: error.response?.statusText,
                        data: error.response?.data,
                        config: {
                            url: error.config?.url,
                            method: error.config?.method,
                            headers: error.config?.headers
                        }
                    });

                    if (error.response?.status === 401) {
                        navigate('/login', { state: { from: `/negotiation-service/${id}` } });
                        return;
                    }

                    // Si es un 404, significa que la negociación no existe y debemos crearla
                    if (error.response?.status === 404) {
                        try {
                            console.log('Negociación no encontrada, creando nueva...');
                            console.log('Intentando crear negociación:', {
                                url: `${API_URL}/api/negociacion-servicio/iniciar/${id}`,
                                data: { creditos_propuestos: 0 }
                            });

                            const createResponse = await axios.post(`${API_URL}/api/negociacion-servicio/iniciar/${id}`, {
                                creditos_propuestos: 0
                            }, {
                                headers: { 
                                    'Authorization': `Bearer ${token}`,
                                    'Content-Type': 'application/json'
                                }
                            });

                            console.log('Respuesta de creación:', createResponse.data);

                            if (createResponse.data.success) {
                                console.log('Nueva negociación creada:', createResponse.data);
                                setNegociacion(createResponse.data.data);
                            } else {
                                throw new Error(createResponse.data.message || 'Error al crear la negociación');
                            }
                        } catch (createError) {
                            console.error('Error al crear negociación:', createError);
                            setError(createError.response?.data?.message || 'Error al crear la negociación');
                        }
                    } else {
                        // Para otros errores, mostrar el mensaje del servidor
                        setError(error.response?.data?.message || 'Error al cargar la negociación');
                    }
                } finally {
                    setLoading(false);
                }
            };

            initializeNegotiation();
            const interval = setInterval(initializeNegotiation, 5000); // Polling cada 5 segundos
            return () => clearInterval(interval);
        }
    }, [id, token, navigate, user]);

    useEffect(() => {
        if (isChatMode && negociacion?.mensajes) {
            scrollToBottom();
        }
    }, [negociacion?.mensajes, mode]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const fetchProfessional = async () => {
        console.log('fetchProfessional llamado:', {
            hasToken: !!token,
            tokenPreview: token ? `${token.substring(0, 10)}...` : 'no token',
            professionalId: id
        });

        if (!token) {
            console.error('No hay token disponible en fetchProfessional');
            return;
        }

        try {
            console.log('Intentando cargar profesional con token:', token.substring(0, 10) + '...');
            const response = await axios.get(`${API_URL}/professionals/${id}`, {
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });
            console.log('Respuesta del servidor:', response.data);
            
            if (response.data.success) {
                console.log('Datos del profesional cargados exitosamente');
                setProfessional(response.data.data);
            } else {
                console.error('Error en la respuesta del servidor:', response.data.message);
                setError(response.data.message || 'Error al cargar los datos del profesional');
            }
        } catch (err) {
            console.error('Error al cargar profesional:', {
                status: err.response?.status,
                message: err.response?.data?.message,
                error: err.message
            });
            
            if (err.response?.status === 401) {
                console.log('Error 401 - Token expirado o inválido');
                // Solo redirigir al login si el token ha expirado
                if (token) {
                    console.log('Redirigiendo a login por token expirado');
                    navigate('/login', { state: { from: `/negotiation-service/${id}` } });
                }
            } else {
                setError(err.response?.data?.message || 'Error al cargar los datos del profesional');
            }
        } finally {
            setLoading(false);
        }
    };

    const handleInitiateNegotiation = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            const response = await axios.post(`${API_URL}/api/negociacion-servicio/iniciar/${id}`, {
                creditos_propuestos: parseInt(creditosPropuestos)
            }, {
                headers: { 
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.data.success) {
                throw new Error(response.data.message || 'Error al iniciar la negociación');
            }

            if (mensajeInicial.trim()) {
                await axios.post(`${API_URL}/api/mensajes`, {
                    contenido: mensajeInicial,
                    id_negociacion_servicio: response.data.data.id
                }, {
                    headers: { Authorization: `Bearer ${token}` }
                });
            }

            navigate(`/negotiation-service/${response.data.data.id}`);
        } catch (error) {
            console.error('Error al iniciar negociación:', error);
            setError(error.response?.data?.message || 'Error al iniciar la negociación');
        } finally {
            setSubmitting(false);
        }
    };

    const handleSendMessage = async (e) => {
        e.preventDefault();
        if (!mensaje.trim()) return;

        setSending(true);
        try {
            const response = await axios.post(`${API_URL}/api/mensajes`, {
                contenido: mensaje,
                id_negociacion_servicio: id
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });

            if (!response.data.success) {
                throw new Error(response.data.message || 'Error al enviar el mensaje');
            }

            setMensaje('');
            // Actualizar la lista de mensajes
            setNegociacion(prev => ({
                ...prev,
                mensajes: [...prev.mensajes, response.data.data]
            }));
        } catch (err) {
            setError(err.response?.data?.message || 'Error al enviar el mensaje');
        } finally {
            setSending(false);
        }
    };

    const handleAction = async (action) => {
        setActionLoading(true);
        try {
            const response = await axios.post(`${API_URL}/api/negociacion-servicio/${id}/${action}`, {}, {
                headers: { Authorization: `Bearer ${token}` }
            });
            
            if (!response.data.success) {
                throw new Error(response.data.message || `Error al ${action} la negociación`);
            }

            if (action === 'completar' && response.data.data.redirect_url) {
                navigate(response.data.data.redirect_url);
                return;
            }

            setNegociacion(prev => ({
                ...prev,
                ...response.data.data
            }));
        } catch (err) {
            setError(err.response?.data?.message || `Error al ${action} la negociación`);
        } finally {
            setActionLoading(false);
        }
    };

    const handleRate = async (e) => {
        e.preventDefault();
        if (puntuacion === 0) {
            setError('Por favor, selecciona una puntuación');
            return;
        }

        setSubmitting(true);
        try {
            const response = await axios.post(`${API_URL}/api/negociacion-servicio/${id}/valorar`, {
                puntuacion,
                comentario
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });

            if (!response.data.success) {
                throw new Error(response.data.message || 'Error al enviar la valoración');
            }

            navigate(`/negotiation-service/${id}`);
        } catch (err) {
            setError(err.response?.data?.message || 'Error al enviar la valoración');
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) {
        return (
            <Container maxWidth="lg" sx={{ mt: 4, textAlign: 'center' }}>
                <CircularProgress />
            </Container>
        );
    }

    if (error) {
        return (
            <Container maxWidth="lg" sx={{ mt: 4 }}>
                <Alert severity="error">{error}</Alert>
            </Container>
        );
    }

    // Renderizado según el modo
    if (isInitiateMode) {
        if (!professional) {
            return (
                <Container maxWidth="md" sx={{ mt: 4 }}>
                    <Alert severity="error">Profesional no encontrado</Alert>
                </Container>
            );
        }

        return (
            <Container maxWidth="md" sx={{ mt: 4 }}>
                <Paper elevation={3} sx={{ p: 4 }}>
                    <Typography variant="h4" component="h1" gutterBottom align="center">
                        Iniciar Negociación de Servicio
                    </Typography>

                    <Grid container spacing={4}>
                        {/* Información del Profesional */}
                        <Grid item xs={12} md={4}>
                            <Box sx={{ textAlign: 'center' }}>
                                {professional.foto_perfil ? (
                                    <Avatar
                                        src={professional.foto_perfil}
                                        alt={professional.name}
                                        sx={{ width: 120, height: 120, mx: 'auto', mb: 2 }}
                                    />
                                ) : (
                                    <Avatar
                                        sx={{ 
                                            width: 120, 
                                            height: 120, 
                                            mx: 'auto', 
                                            mb: 2,
                                            bgcolor: 'primary.main',
                                            fontSize: '3rem'
                                        }}
                                    >
                                        {professional.name.charAt(0).toUpperCase()}
                                    </Avatar>
                                )}
                                <Typography variant="h5" gutterBottom>
                                    {professional.name}
                                </Typography>
                                <Typography variant="subtitle1" color="primary" gutterBottom>
                                    {professional.profession}
                                </Typography>
                                <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                                    {professional.description}
                                </Typography>
                            </Box>
                        </Grid>

                        {/* Formulario de Negociación */}
                        <Grid item xs={12} md={8}>
                            <Box component="form" onSubmit={handleInitiateNegotiation}>
                                <Typography variant="h6" gutterBottom>
                                    Detalles de la Negociación
                                </Typography>

                                <TextField
                                    fullWidth
                                    label="Créditos a proponer"
                                    type="number"
                                    value={creditosPropuestos}
                                    onChange={(e) => setCreditosPropuestos(e.target.value)}
                                    error={creditosPropuestos !== '' && creditosPropuestos <= 0}
                                    helperText={creditosPropuestos !== '' && creditosPropuestos <= 0 ? 
                                        'Los créditos deben ser mayores que 0' : 
                                        'Ingresa la cantidad de créditos que estás dispuesto a pagar'}
                                    sx={{ mb: 3 }}
                                />

                                <TextField
                                    fullWidth
                                    label="Mensaje inicial (opcional)"
                                    multiline
                                    rows={4}
                                    value={mensajeInicial}
                                    onChange={(e) => setMensajeInicial(e.target.value)}
                                    placeholder="Describe brevemente el servicio que necesitas..."
                                    sx={{ mb: 3 }}
                                />

                                <Box sx={{ display: 'flex', gap: 2, justifyContent: 'flex-end' }}>
                                    <Button
                                        variant="outlined"
                                        onClick={() => navigate('/professionals')}
                                    >
                                        Cancelar
                                    </Button>
                                    <Button
                                        type="submit"
                                        variant="contained"
                                        disabled={submitting || !creditosPropuestos || creditosPropuestos <= 0}
                                    >
                                        {submitting ? <CircularProgress size={24} /> : 'Iniciar Negociación'}
                                    </Button>
                                </Box>
                            </Box>
                        </Grid>
                    </Grid>
                </Paper>
            </Container>
        );
    }

    if (isRateMode) {
        if (!negociacion) {
            return (
                <Container maxWidth="md" sx={{ mt: 4 }}>
                    <Alert severity="error">Negociación no encontrada</Alert>
                </Container>
            );
        }

        return (
            <Container maxWidth="sm" sx={{ mt: 4 }}>
                <Paper elevation={3} sx={{ p: 4 }}>
                    <Typography variant="h4" component="h1" gutterBottom align="center">
                        Valorar Servicio
                    </Typography>

                    <Typography variant="body1" gutterBottom>
                        Profesional: {negociacion.profesional.nombre}
                    </Typography>

                    <Typography variant="body1" gutterBottom>
                        Créditos: {negociacion.creditos_propuestos}
                    </Typography>

                    <Box component="form" onSubmit={handleRate} sx={{ mt: 3 }}>
                        <Typography variant="h6" gutterBottom>
                            ¿Cómo calificarías el servicio?
                        </Typography>

                        <Box sx={{ display: 'flex', justifyContent: 'center', mb: 3 }}>
                            <Rating
                                name="simple-controlled"
                                value={puntuacion}
                                onChange={(event, newValue) => {
                                    setPuntuacion(newValue);
                                }}
                                size="large"
                            />
                        </Box>

                        <TextField
                            fullWidth
                            multiline
                            rows={4}
                            label="Comentario (opcional)"
                            value={comentario}
                            onChange={(e) => setComentario(e.target.value)}
                            sx={{ mb: 3 }}
                        />

                        <Button
                            type="submit"
                            variant="contained"
                            color="primary"
                            fullWidth
                            disabled={submitting || puntuacion === 0}
                            sx={{ mt: 2 }}
                        >
                            {submitting ? <CircularProgress size={24} /> : 'Enviar Valoración'}
                        </Button>
                    </Box>
                </Paper>
            </Container>
        );
    }

    // Modo Chat (por defecto)
    if (!negociacion) {
        return (
            <Container maxWidth="lg" sx={{ mt: 4 }}>
                <Alert severity="error">Negociación no encontrada</Alert>
            </Container>
        );
    }

    const isCliente = user.id === negociacion.cliente.id;
    const isProfesional = user.id === negociacion.profesional.id;
    const canSendMessages = negociacion.estado !== 'RECHAZADA';

    return (
        <Container maxWidth="lg" sx={{ mt: 4 }}>
            <Grid container spacing={3}>
                {/* Panel Izquierdo - Información del Profesional y Propuesta */}
                <Grid item xs={12} md={4}>
                    {/* Tarjeta del Profesional */}
                    <Paper elevation={3} sx={{ p: 3, mb: 3 }}>
                        <Box sx={{ textAlign: 'center' }}>
                            {negociacion.profesional.foto_perfil ? (
                                <Avatar
                                    src={negociacion.profesional.foto_perfil}
                                    alt={negociacion.profesional.nombre}
                                    sx={{ width: 120, height: 120, mx: 'auto', mb: 2 }}
                                />
                            ) : (
                                <Avatar
                                    sx={{ 
                                        width: 120, 
                                        height: 120, 
                                        mx: 'auto', 
                                        mb: 2,
                                        bgcolor: 'primary.main',
                                        fontSize: '3rem'
                                    }}
                                >
                                    {negociacion.profesional.nombre.charAt(0).toUpperCase()}
                                </Avatar>
                            )}
                            <Typography variant="h5" gutterBottom>
                                {negociacion.profesional.nombre}
                            </Typography>
                            <Typography variant="subtitle1" color="primary" gutterBottom>
                                {negociacion.profesional.profesion}
                            </Typography>
                            <Chip
                                label={negociacion.estado}
                                color={
                                    negociacion.estado === 'EN_NEGOCIACION' ? 'info' :
                                    negociacion.estado === 'ACEPTADA' ? 'success' :
                                    negociacion.estado === 'COMPLETADA' ? 'primary' :
                                    'error'
                                }
                                sx={{ mt: 1 }}
                            />
                        </Box>
                    </Paper>

                    {/* Formulario de Propuesta */}
                    {negociacion.estado === 'EN_NEGOCIACION' && (
                        <Paper elevation={3} sx={{ p: 3 }}>
                            <Typography variant="h6" gutterBottom>
                                Propuesta de Créditos
                            </Typography>
                            
                            {negociacion.creditos_propuestos && (
                                <Box sx={{ mb: 2, p: 2, bgcolor: 'grey.100', borderRadius: 1 }}>
                                    <Typography variant="subtitle2" color="text.secondary">
                                        Última propuesta:
                                    </Typography>
                                    <Typography variant="h6" color="primary">
                                        {negociacion.creditos_propuestos} créditos
                                    </Typography>
                                </Box>
                            )}

                            {isCliente && (
                                <Box component="form" onSubmit={handleInitiateNegotiation}>
                                    <TextField
                                        fullWidth
                                        label="Nueva propuesta de créditos"
                                        type="number"
                                        value={creditosPropuestos}
                                        onChange={(e) => setCreditosPropuestos(e.target.value)}
                                        error={creditosPropuestos !== '' && creditosPropuestos <= 0}
                                        helperText={creditosPropuestos !== '' && creditosPropuestos <= 0 ? 
                                            'Los créditos deben ser mayores que 0' : 
                                            'Ingresa la cantidad de créditos que estás dispuesto a pagar'}
                                        sx={{ mb: 2 }}
                                    />
                                    <Button
                                        type="submit"
                                        variant="contained"
                                        fullWidth
                                        disabled={submitting || !creditosPropuestos || creditosPropuestos <= 0}
                                    >
                                        {submitting ? <CircularProgress size={24} /> : 'Enviar Propuesta'}
                                    </Button>
                                </Box>
                            )}

                            {isProfesional && negociacion.estado === 'EN_NEGOCIACION' && (
                                <Box sx={{ display: 'flex', gap: 2, mt: 2 }}>
                                    <Button
                                        variant="contained"
                                        color="success"
                                        fullWidth
                                        onClick={() => handleAction('aceptar')}
                                        disabled={actionLoading}
                                    >
                                        {actionLoading ? <CircularProgress size={24} /> : 'Aceptar'}
                                    </Button>
                                    <Button
                                        variant="contained"
                                        color="error"
                                        fullWidth
                                        onClick={() => handleAction('rechazar')}
                                        disabled={actionLoading}
                                    >
                                        {actionLoading ? <CircularProgress size={24} /> : 'Rechazar'}
                                    </Button>
                                </Box>
                            )}

                            {isCliente && negociacion.estado === 'ACEPTADA' && (
                                <Button
                                    variant="contained"
                                    color="primary"
                                    fullWidth
                                    onClick={() => handleAction('completar')}
                                    disabled={actionLoading}
                                    sx={{ mt: 2 }}
                                >
                                    {actionLoading ? <CircularProgress size={24} /> : 'Marcar como Completado'}
                                </Button>
                            )}
                        </Paper>
                    )}
                </Grid>

                {/* Panel Derecho - Chat */}
                <Grid item xs={12} md={8}>
                    <Paper elevation={3} sx={{ p: 2, height: 'calc(100vh - 200px)', display: 'flex', flexDirection: 'column' }}>
                        <Typography variant="h6" gutterBottom>
                            Chat
                        </Typography>
                        <Divider sx={{ mb: 2 }} />

                        <List sx={{ flexGrow: 1, overflow: 'auto', mb: 2 }}>
                            {negociacion.mensajes.map((msg) => (
                                <ListItem
                                    key={msg.id}
                                    sx={{
                                        justifyContent: msg.remitente.id === user.id ? 'flex-end' : 'flex-start'
                                    }}
                                >
                                    <Paper
                                        elevation={1}
                                        sx={{
                                            p: 1,
                                            maxWidth: '70%',
                                            bgcolor: msg.remitente.id === user.id ? 'primary.light' : 'grey.100'
                                        }}
                                    >
                                        <Typography variant="caption" display="block" color="textSecondary">
                                            {msg.remitente.nombre}
                                        </Typography>
                                        <Typography variant="body1">
                                            {msg.contenido}
                                        </Typography>
                                        <Typography variant="caption" display="block" color="textSecondary" align="right">
                                            {new Date(msg.fecha_envio).toLocaleString()}
                                        </Typography>
                                    </Paper>
                                </ListItem>
                            ))}
                            <div ref={messagesEndRef} />
                        </List>

                        {canSendMessages && (
                            <Box sx={{ display: 'flex', gap: 1, mt: 'auto' }}>
                                <TextField
                                    fullWidth
                                    variant="outlined"
                                    placeholder="Escribe tu mensaje..."
                                    value={mensaje}
                                    onChange={(e) => setMensaje(e.target.value)}
                                    onKeyPress={(e) => {
                                        if (e.key === 'Enter' && !e.shiftKey) {
                                            e.preventDefault();
                                            handleSendMessage();
                                        }
                                    }}
                                />
                                <IconButton 
                                    color="primary" 
                                    onClick={handleSendMessage}
                                    disabled={!mensaje.trim() || sending}
                                    sx={{ 
                                        fontSize: '1.5rem',
                                        width: 48,
                                        height: 48
                                    }}
                                >
                                    📤
                                </IconButton>
                            </Box>
                        )}
                    </Paper>
                </Grid>
            </Grid>
        </Container>
    );
};

export default ProfessionalNegotiation; 