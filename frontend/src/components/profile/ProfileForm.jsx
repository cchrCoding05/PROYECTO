import React, { useState, useEffect } from 'react';
import { useAuth } from '../../hooks/useAuth';
import { userService } from '../../services/userService';
import AlertMessage from '../Layout/AlertMessage';
import { Cloudinary } from '@cloudinary/url-gen';
import { AdvancedImage } from '@cloudinary/react';
import { useNavigate, useLocation } from 'react-router-dom';
import { Form, Button, Container, Row, Col, Alert, Card, Tab, Tabs } from 'react-bootstrap';
import MyProducts from '../Products/MyProducts';
import MyNegotiations from './MyNegotiations';
import MyChats from '../negotiation/MyChats';
import './Profile.css';

const ProfileForm = () => {
  const { currentUser } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState('profile');
  const [profileData, setProfileData] = useState({
    username: '',
    description: '',
    profession: ''
  });
  const [avatar, setAvatar] = useState(null);
  const [previewAvatar, setPreviewAvatar] = useState('');
  const [alert, setAlert] = useState(null);
  const [loading, setLoading] = useState(false);

  // Inicializar Cloudinary
  const cld = new Cloudinary({
    cloud: {
      cloudName: import.meta.env.VITE_CLOUDINARY_CLOUD_NAME
    }
  });

  useEffect(() => {
    if (currentUser?.data) {
      console.log('Datos del usuario actual:', currentUser.data);
      setProfileData({
        username: currentUser.data.username || '',
        description: currentUser.data.description || '',
        profession: currentUser.data.profession || ''
      });
      
      if (currentUser.data.profilePhoto) {
        console.log('URL de la foto de perfil:', currentUser.data.profilePhoto);
        setPreviewAvatar(currentUser.data.profilePhoto);
      }
    }
  }, [currentUser]);

  useEffect(() => {
    // Obtener el tab de la URL
    const params = new URLSearchParams(location.search);
    const tab = params.get('tab');
    if (tab && ['profile', 'products', 'negotiations', 'chats'].includes(tab)) {
      setActiveTab(tab);
    }
  }, [location.search]);

  const handleTabSelect = (tab) => {
    setActiveTab(tab);
    // Actualizar la URL sin recargar la página
    navigate(`/profile?tab=${tab}`, { replace: true });
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setProfileData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleAvatarChange = async (e) => {
    const file = e.target.files[0];
    if (file) {
      setLoading(true);
      try {
        // Crear FormData para Cloudinary
        const formData = new FormData();
        formData.append('file', file);
        formData.append('upload_preset', import.meta.env.VITE_CLOUDINARY_UPLOAD_PRESET);

        // Subir imagen a Cloudinary
        const response = await fetch(
          `https://api.cloudinary.com/v1_1/${import.meta.env.VITE_CLOUDINARY_CLOUD_NAME}/image/upload`,
          {
            method: 'POST',
            body: formData,
          }
        );

        const result = await response.json();
        console.log('Resultado de Cloudinary:', result);
        
        if (result.secure_url) {
          console.log('URL segura de Cloudinary:', result.secure_url);
          setPreviewAvatar(result.secure_url);
          // Actualizar el perfil con la nueva URL de la imagen
          const updateResponse = await userService.updateProfile({
            ...profileData,
            profilePhoto: result.secure_url
          });
          console.log('Respuesta de actualización:', updateResponse);
          
          if (updateResponse.success) {
            setAlert({
              type: 'success',
              message: 'Foto de perfil actualizada correctamente'
            });
          }
        }
      } catch (error) {
        console.error('Error al subir la imagen:', error);
        setAlert({
          type: 'danger',
          message: 'Error al subir la imagen: ' + error.message
        });
      } finally {
        setLoading(false);
      }
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setAlert(null);
    
    try {
      const response = await userService.updateProfile(profileData);
      
      if (response.success) {
        setAlert({
          type: 'success',
          message: 'Perfil actualizado correctamente'
        });
      } else {
        throw new Error(response.message || 'Error al actualizar el perfil');
      }
    } catch (error) {
      setAlert({
        type: 'danger',
        message: error.message || 'Error al actualizar el perfil'
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container className="mt-4">
      <h2>Mi Perfil</h2>
      
      <Tabs activeKey={activeTab} onSelect={handleTabSelect} className="mb-4">
        <Tab eventKey="profile" title="Datos Personales">
          <Card>
            <Card.Body>
              {alert && (
                <AlertMessage
                  message={alert.message}
                  type={alert.type}
                  onClose={() => setAlert(null)}
                />
              )}

              <Form onSubmit={handleSubmit}>
                {/* Fila principal: Avatar + Campos de usuario y profesión */}
                <div className="profile-header-row">
                  {/* Columna del avatar (20%) */}
                  <div className="profile-avatar-col">
                    <div className="profile-avatar-section">
                      <div className="avatar-preview">
                        {previewAvatar ? (
                          <img
                            src={previewAvatar}
                            alt="Avatar"
                            className="profile-avatar"
                          />
                        ) : (
                          <div className="avatar-placeholder">
                            <span>{profileData.username.charAt(0)}</span>
                          </div>
                        )}
                      </div>
                      <div className="avatar-actions">
                        <label htmlFor="avatar" className="avatar-upload-btn">
                          {loading ? 'Subiendo...' : 'Cambiar avatar'}
                        </label>
                        <input
                          type="file"
                          id="avatar"
                          name="avatar"
                          accept="image/*"
                          onChange={handleAvatarChange}
                          className="hidden-input"
                          disabled={loading}
                        />
                      </div>
                    </div>
                  </div>

                  {/* Columna de información personal (80%) */}
                  <div className="profile-info-col">
                    <div className="profile-info-fields">
                      <div className="form-group">
                        <label htmlFor="username">Usuario</label>
                        <input
                          type="text"
                          id="username"
                          name="username"
                          value={profileData.username}
                          onChange={handleChange}
                          className="form-control profile-info-input"
                          required
                        />
                      </div>
                      <div className="form-group">
                        <label htmlFor="profession">Profesión</label>
                        <input
                          type="text"
                          id="profession"
                          name="profession"
                          value={profileData.profession}
                          onChange={handleChange}
                          className="form-control profile-info-input"
                        />
                      </div>
                    </div>
                  </div>
                </div>

                {/* Descripción debajo de todo */}
                <div className="form-group profile-description-row">
                  <label htmlFor="description">Descripción</label>
                  <textarea
                    id="description"
                    name="description"
                    value={profileData.description}
                    onChange={handleChange}
                    className="form-control description-textarea"
                    rows="6"
                    placeholder="Descríbete y tus servicios aquí..."
                  />
                </div>

                {/* Botón de guardar */}
                <div className="form-group mt-3">
                  <button 
                    type="submit" 
                    className="btn btn-primary" 
                    disabled={loading}
                  >
                    {loading ? 'Guardando...' : 'Guardar cambios'}
                  </button>
                </div>
              </Form>
            </Card.Body>
          </Card>
        </Tab>

        <Tab eventKey="products" title="Mis Productos">
          <MyProducts />
        </Tab>

        <Tab eventKey="negotiations" title="Mis Negociaciones">
          <MyNegotiations />
        </Tab>

        <Tab eventKey="chats" title="Mis Chats">
          <MyChats />
        </Tab>
      </Tabs>
    </Container>
  );
};

export default ProfileForm;