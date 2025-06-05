import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { productService } from '../../services/productService';
import AlertMessage from '../Layout/AlertMessage';
import './ProductUpload.css';

const ProductUpload = () => {
  const [productData, setProductData] = useState({
    titulo: '',
    descripcion: '',
    creditos: ''
  });
  const [image, setImage] = useState(null);
  const [previewImage, setPreviewImage] = useState('');
  const [loading, setLoading] = useState(false);
  const [alert, setAlert] = useState(null);
  const navigate = useNavigate();

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
      if (!productData.titulo.trim()) {
        setAlert({
          type: 'danger',
          message: 'El nombre del producto es obligatorio'
        });
        setLoading(false);
        return;
      }

      if (!productData.descripcion.trim()) {
        setAlert({
          type: 'danger',
          message: 'La descripción es obligatoria'
        });
        setLoading(false);
        return;
      }

      if (!productData.creditos || isNaN(parseInt(productData.creditos)) || parseInt(productData.creditos) <= 0) {
        setAlert({
          type: 'danger',
          message: 'El precio debe ser un número mayor que 0'
        });
        setLoading(false);
        return;
      }

      // Validar imagen
      if (!image) {
        setAlert({
          type: 'danger',
          message: 'Por favor, selecciona una imagen para el producto'
        });
        setLoading(false);
        return;
      }

      // Primero subir la imagen a Cloudinary
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
        titulo: productData.titulo.trim(),
        descripcion: productData.descripcion.trim(),
        creditos: parseInt(productData.creditos),
        imagen: uploadResult.secure_url
      });

      const productResponse = await productService.create({
        titulo: productData.titulo.trim(),
        descripcion: productData.descripcion.trim(),
        creditos: parseInt(productData.creditos),
        imagen: uploadResult.secure_url
      });

      console.log('Respuesta del servidor:', productResponse);

      if (productResponse.success) {
        setAlert({
          type: 'success',
          message: 'Producto subido correctamente'
        });
        // Limpiar el formulario
        setProductData({
          titulo: '',
          descripcion: '',
          creditos: ''
        });
        setImage(null);
        setPreviewImage('');
        navigate('/profile?tab=products');
      } else {
        throw new Error(productResponse.message || 'Error al crear el producto');
      }
    } catch (error) {
      console.error('Error en handleSubmit:', error);
      setAlert({
        type: 'danger',
        message: error.message || 'Error al subir el producto'
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
            <label htmlFor="titulo">Nombre del Producto</label>
            <input
              type="text"
              id="titulo"
              name="titulo"
              value={productData.titulo}
              onChange={handleChange}
              className="form-control"
              placeholder="Nombre del producto"
            />
          </div>

          <div className="form-group">
            <label htmlFor="descripcion">Descripción</label>
            <textarea
              id="descripcion"
              name="descripcion"
              value={productData.descripcion}
              onChange={handleChange}
              className="form-control"
              rows="4"
              placeholder="Describe tu producto"
            />
          </div>

          <div className="form-row">
            <div className="form-group col-md-6">
              <label htmlFor="creditos">Precio (créditos)</label>
              <input
                type="number"
                id="creditos"
                name="creditos"
                value={productData.creditos}
                onChange={handleChange}
                className="form-control"
                step="1"
                min="1"
                placeholder="Precio en créditos"
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