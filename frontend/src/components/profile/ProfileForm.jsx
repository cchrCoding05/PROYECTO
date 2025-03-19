import React, { useState, useEffect } from 'react';
import { useAuth } from '../../hooks/useAuth';
import { userService } from '../../services/api';
import AlertMessage from '../Layout/AlertMessage';
import './Profile.css';

const ProfileForm = () => {
  const { currentUser } = useAuth();
  const [profileData, setProfileData] = useState({
    username: '',
    description: ''
  });
  const [avatar, setAvatar] = useState(null);
  const [previewAvatar, setPreviewAvatar] = useState('');
  const [alert, setAlert] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (currentUser) {
      setProfileData({
        username: currentUser.username || '',
        description: currentUser.description || ''
      });
      
      if (currentUser.avatarUrl) {
        setPreviewAvatar(currentUser.avatarUrl);
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

  const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      setAvatar(file);
      const reader = new FileReader();
      reader.onloadend = () => {
        setPreviewAvatar(reader.result);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setAlert(null);
    
    try {
      // Actualizar el perfil
      const profileResponse = await userService.updateProfile(profileData);
      
      // Si hay un avatar nuevo, enviarlo
      if (avatar) {
        const formData = new FormData();
        formData.append('avatar', avatar);
        await userService.updateAvatar(formData);
      }
      
      setAlert({
        type: 'success',
        message: 'Perfil actualizado correctamente'
      });
    } catch (error) {
      setAlert({
        type: 'danger',
        message: 'Error al actualizar el perfil: ' + error.toString()
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
                    <img src={previewAvatar} alt="Avatar" />
                  ) : (
                    <div className="avatar-placeholder">
                      <span>{profileData.username.charAt(0)}</span>
                    </div>
                  )}
                </div>
                
                <label htmlFor="avatar" className="avatar-upload-btn">
                  Cambiar avatar
                </label>
                <input
                  type="file"
                  id="avatar"
                  name="avatar"
                  accept="image/*"
                  onChange={handleAvatarChange}
                  className="hidden-input"
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