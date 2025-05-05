import React, { useState, useEffect } from 'react';
import { useAuth } from '../../hooks/useAuth';
import { userService } from '../../services/api';
import AlertMessage from '../Layout/AlertMessage';
import { Cloudinary } from '@cloudinary/url-gen';
import { AdvancedImage } from '@cloudinary/react';
import './Profile.css';

const ProfileForm = () => {
  const { currentUser } = useAuth();
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
    <div className="profile-container">
      <div className="profile-card">
        <h2>Personaliza tu perfil</h2>
        
        {alert && (
          <AlertMessage 
            message={alert.message} 
            type={alert.type} 
            onClose={() => setAlert(null)} 
          />
        )}
        
        <form onSubmit={handleSubmit}>
          <div className="profile-layout">
            <div className="profile-col">
              <div className="form-group">
                <label htmlFor="username">Usuario</label>
                <input
                  type="text"
                  id="username"
                  name="username"
                  value={profileData.username}
                  onChange={handleChange}
                  className="form-control"
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
                  className="form-control"
                />
              </div>
              
              <div className="form-group">
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
            </div>
            
            <div className="profile-col">
              <div className="avatar-container">
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
          
          <button 
            type="submit" 
            className="btn-primary" 
            disabled={loading}
          >
            {loading ? 'Guardando...' : 'Guardar cambios'}
          </button>
        </form>
      </div>
    </div>
  );
};

export default ProfileForm; 