import React, { useState, useEffect } from 'react';
import {
    Box,
    Table,
    Thead,
    Tbody,
    Tr,
    Th,
    Td,
    Button,
    Input,
    useToast,
    Modal,
    ModalOverlay,
    ModalContent,
    ModalHeader,
    ModalBody,
    ModalCloseButton,
    FormControl,
    FormLabel,
    VStack,
    useDisclosure,
    Image,
    Select,
    InputGroup,
    InputLeftElement
} from '@chakra-ui/react';
import { adminService } from '../../services/adminService';

const ProductManagement = () => {
    const [products, setProducts] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedProduct, setSelectedProduct] = useState(null);
    const { isOpen, onOpen, onClose } = useDisclosure();
    const toast = useToast();
    const [sortConfig, setSortConfig] = useState({ key: null, direction: 'asc' });

    const fetchProducts = async () => {
        try {
            const response = await adminService.getAllProducts();
            console.log('Respuesta de getAllProducts:', response);
            if (response.success && Array.isArray(response.data)) {
                setProducts(response.data);
            } else {
                console.error('Formato de respuesta inválido:', response);
                toast({
                    title: 'Error',
                    description: 'Formato de datos inválido',
                    status: 'error',
                    duration: 3000,
                    isClosable: true,
                });
            }
        } catch (error) {
            console.error('Error al cargar productos:', error);
            toast({
                title: 'Error',
                description: 'No se pudieron cargar los productos',
                status: 'error',
                duration: 3000,
                isClosable: true,
            });
        }
    };

    useEffect(() => {
        fetchProducts();
    }, [searchTerm]);

    const handleDelete = async (productId) => {
        if (window.confirm('¿Estás seguro de que deseas eliminar este producto?')) {
            try {
                const response = await adminService.deleteProduct(productId);
                if (response.success) {
                    toast({
                        title: 'Éxito',
                        description: 'Producto eliminado correctamente',
                        status: 'success',
                        duration: 3000,
                        isClosable: true,
                    });
                    fetchProducts();
                }
            } catch (error) {
                toast({
                    title: 'Error',
                    description: error.message || 'No se pudo eliminar el producto',
                    status: 'error',
                    duration: 3000,
                    isClosable: true,
                });
            }
        }
    };

    const handleEdit = (product) => {
        setSelectedProduct(product);
        onOpen();
    };

    const handleSave = async (e) => {
        e.preventDefault();
        try {
            const response = await adminService.updateProduct(selectedProduct.id, {
                name: selectedProduct.name,
                credits: selectedProduct.credits,
                state: selectedProduct.state
            });
            
            if (response.success) {
                toast({
                    title: 'Éxito',
                    description: 'Producto actualizado correctamente',
                    status: 'success',
                    duration: 3000,
                    isClosable: true,
                });
                onClose();
                fetchProducts();
            }
        } catch (error) {
            toast({
                title: 'Error',
                description: error.message || 'No se pudo actualizar el producto',
                status: 'error',
                duration: 3000,
                isClosable: true,
            });
        }
    };

    const handleSort = (key) => {
        let direction = 'asc';
        if (sortConfig.key === key && sortConfig.direction === 'asc') {
            direction = 'desc';
        }
        setSortConfig({ key, direction });
    };

    const getSortedProducts = () => {
        if (!sortConfig.key) return products;

        return [...products].sort((a, b) => {
            let aValue = a[sortConfig.key];
            let bValue = b[sortConfig.key];

            if (aValue < bValue) {
                return sortConfig.direction === 'asc' ? -1 : 1;
            }
            if (aValue > bValue) {
                return sortConfig.direction === 'asc' ? 1 : -1;
            }
            return 0;
        });
    };

    const getSortIndicator = (columnKey) => {
        if (sortConfig.key !== columnKey) return '';
        return sortConfig.direction === 'asc' ? ' ↑' : ' ↓';
    };

    const getEstadoText = (estado) => {
        switch (estado) {
            case 1:
                return 'Disponible';
            case 2:
                return 'Reservado';
            case 3:
                return 'Intercambiado';
            default:
                return 'Desconocido';
        }
    };

    return (
        <Box p={5}>
            <InputGroup mb={4}>
                <InputLeftElement pointerEvents="none">
                    🔍
                </InputLeftElement>
                <Input
                    placeholder="Buscar productos..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
            </InputGroup>

            <Table variant="simple">
                <Thead>
                    <Tr>
                        <Th>Imagen</Th>
                        <Th 
                            cursor="pointer" 
                            onClick={() => handleSort('name')}
                            _hover={{ bg: 'gray.100' }}
                        >
                            Nombre{getSortIndicator('name')}
                        </Th>
                        <Th 
                            cursor="pointer" 
                            onClick={() => handleSort('credits')}
                            _hover={{ bg: 'gray.100' }}
                        >
                            Créditos{getSortIndicator('credits')}
                        </Th>
                        <Th 
                            cursor="pointer" 
                            onClick={() => handleSort('state')}
                            _hover={{ bg: 'gray.100' }}
                        >
                            Estado{getSortIndicator('state')}
                        </Th>
                        <Th>Acciones</Th>
                    </Tr>
                </Thead>
                <Tbody>
                    {getSortedProducts().map((product) => (
                        <Tr key={product.id}>
                            <Td>
                                <Image
                                    src={product.image || 'https://via.placeholder.com/50'}
                                    alt={product.name}
                                    boxSize="50px"
                                    objectFit="cover"
                                    borderRadius="md"
                                />
                            </Td>
                            <Td>{product.name}</Td>
                            <Td>{product.credits}</Td>
                            <Td>{getEstadoText(product.state)}</Td>
                            <Td>
                                <Button
                                    colorScheme="blue"
                                    size="sm"
                                    mr={2}
                                    onClick={() => handleEdit(product)}
                                >
                                    Editar
                                </Button>
                                <Button
                                    colorScheme="red"
                                    size="sm"
                                    onClick={() => handleDelete(product.id)}
                                >
                                    Eliminar
                                </Button>
                            </Td>
                        </Tr>
                    ))}
                </Tbody>
            </Table>

            <Modal isOpen={isOpen} onClose={onClose}>
                <ModalOverlay />
                <ModalContent>
                    <ModalHeader>Editar Producto</ModalHeader>
                    <ModalCloseButton />
                    <ModalBody>
                        <form onSubmit={handleSave}>
                            <VStack spacing={4}>
                                <FormControl>
                                    <FormLabel>Nombre del Producto</FormLabel>
                                    <Input
                                        value={selectedProduct?.name || ''}
                                        onChange={(e) =>
                                            setSelectedProduct({
                                                ...selectedProduct,
                                                name: e.target.value,
                                            })
                                        }
                                    />
                                </FormControl>
                                <FormControl>
                                    <FormLabel>Créditos</FormLabel>
                                    <Input
                                        type="number"
                                        value={selectedProduct?.credits || 0}
                                        onChange={(e) =>
                                            setSelectedProduct({
                                                ...selectedProduct,
                                                credits: parseInt(e.target.value),
                                            })
                                        }
                                    />
                                </FormControl>
                                <FormControl>
                                    <FormLabel>Estado</FormLabel>
                                    <Select
                                        value={selectedProduct?.state || ''}
                                        onChange={(e) =>
                                            setSelectedProduct({
                                                ...selectedProduct,
                                                state: parseInt(e.target.value),
                                            })
                                        }
                                    >
                                        <option value="1">Disponible</option>
                                        <option value="2">Reservado</option>
                                        <option value="3">Intercambiado</option>
                                    </Select>
                                </FormControl>
                                <Button type="submit" colorScheme="blue" width="full">
                                    Guardar Cambios
                                </Button>
                            </VStack>
                        </form>
                    </ModalBody>
                </ModalContent>
            </Modal>
        </Box>
    );
};

export default ProductManagement; 