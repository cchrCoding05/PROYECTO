import React, { useState } from 'react';
import { productService } from '../../services/api';
import AlertMessage from '../Layout/AlertMessage';
import './ProductUpload.css';

const ProductUpload = () => {
  const [productData, setProductData] = useState({
    name: '',
    description: '',
    price: ''
  });
  const [image, setImage] = useState(null);
  const [previewImage, setPreviewImage] = useState('');
  const [loading, setLoading] = useState(false);
  const [alert, setAlert] = useState(null);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setProductData(prev => ({
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
    setLoading(true);
    setAlert(null);

    try {
      // Validar campos obligatorios
      if (!productData.name.trim()) {
        throw new Error('El nombre del producto es obligatorio');
      }
      if (!productData.description.trim()) {
        throw new Error('La descripción es obligatoria');
      }
      if (!productData.price || isNaN(parseInt(productData.price)) || parseInt(productData.price) <= 0) {
        throw new Error('El precio debe ser un número mayor que 0');
      }

      // Primero subir la imagen a Cloudinary
      if (!image) {
        throw new Error('Por favor, selecciona una imagen para el producto');
      }

      console.log('Subiendo imagen a Cloudinary...');
      const formData = new FormData();
      formData.append('file', image);
      formData.append('upload_preset', import.meta.env.VITE_CLOUDINARY_UPLOAD_PRESET);

      const uploadResponse = await fetch(
        `https://api.cloudinary.com/v1_1/${import.meta.env.VITE_CLOUDINARY_CLOUD_NAME}/image/upload`,
        {
          method: 'POST',
          body: formData,
        }
      );

      const uploadResult = await uploadResponse.json();
      console.log('Resultado de Cloudinary:', uploadResult);
      
      if (!uploadResult.secure_url) {
        throw new Error('Error al subir la imagen');
      }

      // Crear el producto con la URL de la imagen
      console.log('Creando producto con datos:', {
        name: productData.name.trim(),
        description: productData.description.trim(),
        price: parseInt(productData.price),
        image: uploadResult.secure_url
      });

      const productResponse = await productService.create({
        name: productData.name.trim(),
        description: productData.description.trim(),
        price: parseInt(productData.price),
        image: uploadResult.secure_url
      });

      console.log('Respuesta del servidor:', productResponse);

      if (productResponse.success) {
        setAlert({
          type: 'success',
          message: 'Producto subido correctamente'
        });
        // Limpiar el formulario
        setProductData({
          name: '',
          description: '',
          price: ''
        });
        setImage(null);
        setPreviewImage('');
      } else {
        throw new Error(productResponse.message || 'Error al crear el producto');
      }
    } catch (error) {
      console.error('Error en handleSubmit:', error);
      let errorMessage = 'Error al subir el producto';
      
      if (error.response) {
        console.error('Detalles del error:', error.response);
        errorMessage = error.response.message || errorMessage;
      } else if (error.message) {
        errorMessage = error.message;
      }
      
      setAlert({
        type: 'danger',
        message: errorMessage
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="product-upload-container">
      <div className="product-upload-card">
        <h2>Subir Nuevo Producto</h2>
        
        {alert && (
          <AlertMessage 
            message={alert.message} 
            type={alert.type} 
            onClose={() => setAlert(null)} 
          />
        )}
        
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="name">Nombre del Producto</label>
            <input
              type="text"
              id="name"
              name="name"
              value={productData.name}
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
              value={productData.description}
              onChange={handleChange}
              className="form-control"
              rows="4"
              required
            />
          </div>

          <div className="form-row">
            <div className="form-group col-md-6">
              <label htmlFor="price">Precio (€)</label>
              <input
                type="number"
                id="price"
                name="price"
                value={productData.price}
                onChange={handleChange}
                className="form-control"
                step="0.01"
                min="0"
                required
              />
            </div>
          </div>

          <div className="form-group">
            <label htmlFor="image">Imagen del Producto</label>
            <div className="image-upload-container">
              {previewImage ? (
                <img
                  src={previewImage}
                  alt="Vista previa"
                  className="image-preview"
                />
              ) : (
                <div className="image-placeholder">
                  <i className="bi bi-image"></i>
                  <span>Selecciona una imagen</span>
                </div>
              )}
              <input
                type="file"
                id="image"
                name="image"
                accept="image/*"
                onChange={handleImageChange}
                className="image-input"
                required
              />
            </div>
          </div>

          <button 
            type="submit" 
            className="btn btn-primary w-100"
            disabled={loading}
          >
            {loading ? 'Subiendo...' : 'Subir Producto'}
          </button>
        </form>
      </div>
    </div>
  );
};

export default ProductUpload; 