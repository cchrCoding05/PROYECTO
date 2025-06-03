import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { productService } from '../../services/productService';
import { useAuth } from '../../hooks/useAuth';
import AlertMessage from '../Layout/AlertMessage';
import { Button, Form } from 'react-bootstrap';
import './ProductDetails.css';

const EditProduct = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated, user, loading: authLoading } = useAuth();
  
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);
  const [image, setImage] = useState(null);
  const [previewImage, setPreviewImage] = useState('');
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    credits: '',
    image: '',
    state: 1
  });

  // Cargar el producto
  useEffect(() => {
    const loadProduct = async () => {
      if (authLoading) return;

      if (!isAuthenticated || !user) {
        navigate('/login', { state: { from: `/edit-product/${id}` } });
        return;
      }

      try {
        console.log('Iniciando carga de producto:', { id, userId: user.id });
        setLoading(true);
        setError(null);
        
        const result = await productService.getById(id);
        console.log('Respuesta del servidor:', result);
        
        if (!result.success) {
          throw new Error(result.message || 'Error al cargar el producto');
        }
        
        const productData = result.data;
        setFormData({
          name: productData.name || productData.title || '',
          description: productData.description || '',
          credits: productData.credits || 0,
          image: productData.image || '',
          state: productData.state || 1
        });
        setPreviewImage(productData.image || '');
      } catch (err) {
        console.error('Error al cargar producto:', err);
        setError(err.message || 'Error al cargar el producto');
      } finally {
        setLoading(false);
      }
    };

    loadProduct();
  }, [id, isAuthenticated, user, authLoading, navigate]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      setImage(file);
      // Crear URL de vista previa
      const reader = new FileReader();
      reader.onloadend = () => {
        setPreviewImage(reader.result);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setSuccess(false);
    setLoading(true);

    try {
      // Validaciones básicas
      if (!formData.name.trim()) {
        setError('El nombre del producto es requerido');
        setLoading(false);
        return;
      }

      if (!formData.credits || parseInt(formData.credits) < 1) {
        setError('El precio debe ser al menos 1 crédito');
        setLoading(false);
        return;
      }

      let imageUrl = formData.image;

      // Si hay una nueva imagen, subirla a Cloudinary
      if (image) {
        console.log('Subiendo imagen a Cloudinary...');
        
        // Crear FormData para Cloudinary (usando nombre diferente para evitar conflicto)
        const cloudinaryFormData = new FormData();
        cloudinaryFormData.append('file', image);
        cloudinaryFormData.append('upload_preset', import.meta.env.VITE_CLOUDINARY_UPLOAD_PRESET);

        const uploadResponse = await fetch(
          `https://api.cloudinary.com/v1_1/${import.meta.env.VITE_CLOUDINARY_CLOUD_NAME}/image/upload`,
          {
            method: 'POST',
            body: cloudinaryFormData,
          }
        );

        const uploadResult = await uploadResponse.json();
        console.log('Resultado de Cloudinary:', uploadResult);
        
        if (!uploadResult.secure_url) {
          throw new Error('Error al subir la imagen');
        }

        imageUrl = uploadResult.secure_url;
      }

      // Actualizar el producto
      const result = await productService.updateProduct(id, {
        name: formData.name,
        description: formData.description,
        credits: parseInt(formData.credits),
        image: imageUrl,
        state: parseInt(formData.state)
      });
      
      if (result && result.success === false) {
        setError(result.message || 'Error al actualizar el producto');
        return;
      }

      setSuccess(true);
      // Actualizar la imagen de vista previa con la nueva URL
      if (imageUrl !== formData.image) {
        setFormData(prev => ({ ...prev, image: imageUrl }));
      }
      
      // Redirigir a la lista de productos después de 2 segundos
      setTimeout(() => {
        navigate('/profile?tab=products');
      }, 2000);
    } catch (err) {
      console.error('Error al actualizar producto:', err);
      setError(err.message || 'Error al actualizar el producto');
    } finally {
      setLoading(false);
    }
  };

  if (authLoading || loading) {
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

  if (!isAuthenticated || !user) {
    return (
      <div className="container py-4">
        <AlertMessage 
          message="Debes iniciar sesión para editar productos" 
          type="warning" 
        />
        <div className="text-center mt-3">
          <Button 
            variant="primary" 
            onClick={() => navigate('/login', { state: { from: `/edit-product/${id}` } })}
          >
            Ir a iniciar sesión
          </Button>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container py-4">
        <AlertMessage 
          message={error} 
          type="danger" 
        />
        <div className="text-center mt-3">
          <Button 
            variant="secondary" 
            onClick={() => navigate(`/negotiation/${id}`)}
          >
            Volver a la negociación
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="container py-4">
      <div className="row justify-content-center">
        <div className="col-md-8">
          <div className="card shadow">
            <div className="card-body">
              <h2 className="card-title text-center mb-4">Editar Producto</h2>
              
              {success && (
                <AlertMessage 
                  message="Producto actualizado con éxito" 
                  type="success" 
                />
              )}

              <form onSubmit={handleSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Nombre del Producto</Form.Label>
                  <Form.Control
                    type="text"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    placeholder="Nombre del producto"
                  />
                </Form.Group>

                <Form.Group className="mb-3">
                  <Form.Label>Descripción</Form.Label>
                  <Form.Control
                    as="textarea"
                    rows={3}
                    name="description"
                    value={formData.description}
                    onChange={handleChange}
                    placeholder="Describe tu producto"
                  />
                </Form.Group>

                <Form.Group className="mb-3">
                  <Form.Label>Precio (créditos)</Form.Label>
                  <Form.Control
                    type="number"
                    name="credits"
                    value={formData.credits}
                    onChange={handleChange}
                    min="1"
                    step="1"
                    placeholder="Precio en créditos"
                  />
                </Form.Group>

                <Form.Group className="mb-3">
                  <Form.Label>Estado del Producto</Form.Label>
                  <Form.Select
                    name="state"
                    value={formData.state}
                    onChange={handleChange}
                  >
                    <option value={1}>Disponible</option>
                    <option value={3}>Intercambiado</option>
                  </Form.Select>
                </Form.Group>

                <Form.Group className="mb-3">
                  <Form.Label>Imagen del Producto</Form.Label>
                  <div className="image-upload-container">
                    <div className="image-preview-container mb-3">
                      {previewImage ? (
                        <img
                          src={previewImage}
                          alt="Vista previa"
                          className="image-preview"
                          style={{ 
                            maxWidth: '200px', 
                            maxHeight: '200px', 
                            objectFit: 'contain',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            padding: '4px',
                            display: 'block'
                          }}
                        />
                      ) : (
                        <div 
                          className="image-placeholder d-flex flex-column align-items-center justify-content-center"
                          style={{
                            width: '200px',
                            height: '150px',
                            border: '2px dashed #ddd',
                            borderRadius: '4px',
                            backgroundColor: '#f8f9fa'
                          }}
                        >
                          <i className="bi bi-image fs-1 text-muted"></i>
                          <span className="text-muted">Selecciona una imagen</span>
                        </div>
                      )}
                    </div>
                    
                    <Form.Control
                      type="file"
                      accept="image/*"
                      onChange={handleImageChange}
                      className="d-none"
                      id="imageUpload"
                    />
                  </div>
                  
                  {/* Botones fuera del contenedor de imagen */}
                  <div className="d-flex gap-2 mb-2">
                    <Button 
                      variant="outline-primary" 
                      size="sm"
                      onClick={() => document.getElementById('imageUpload').click()}
                      type="button"
                    >
                      <i className="bi bi-upload me-1"></i>
                      Seleccionar Imagen
                    </Button>
                    
                    {image && (
                      <Button 
                        variant="outline-danger" 
                        size="sm"
                        onClick={() => {
                          setImage(null);
                          setPreviewImage(formData.image);
                          document.getElementById('imageUpload').value = '';
                        }}
                        type="button"
                      >
                        <i className="bi bi-x-circle me-1"></i>
                        Cancelar
                      </Button>
                    )}
                  </div>
                  
                  {image && (
                    <small className="text-success d-block">
                      <i className="bi bi-check-circle me-1"></i>
                      Nueva imagen seleccionada: {image.name}
                    </small>
                  )}
                </Form.Group>

                <div className="d-grid gap-2">
                  <Button 
                    variant="primary" 
                    type="submit"
                    className="mb-2"
                    disabled={loading}
                  >
                    {loading ? 'Guardando...' : 'Guardar Cambios'}
                  </Button>
                  <Button 
                    variant="secondary" 
                    onClick={() => navigate('/profile?tab=products')}
                    disabled={loading}
                  >
                    Cancelar
                  </Button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default EditProduct;