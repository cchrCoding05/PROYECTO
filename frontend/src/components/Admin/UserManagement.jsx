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
    InputGroup,
    InputLeftElement,
    Flex,
    IconButton,
    Tooltip
} from '@chakra-ui/react';
import { adminService } from '../../services/api';

const UserManagement = () => {
    const [users, setUsers] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedUser, setSelectedUser] = useState(null);
    const { isOpen, onOpen, onClose } = useDisclosure();
    const toast = useToast();
    const [sortConfig, setSortConfig] = useState({ key: null, direction: 'asc' });

    const fetchUsers = async () => {
        try {
            const data = await adminService.getAllUsers();
            setUsers(data);
        } catch (error) {
            toast({
                title: 'Error',
                description: 'No se pudieron cargar los usuarios',
                status: 'error',
                duration: 3000,
                isClosable: true,
            });
        }
    };

    useEffect(() => {
        fetchUsers();
    }, [searchTerm]);

    const handleDelete = async (userId) => {
        if (window.confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.')) {
            try {
                await adminService.deleteUser(userId);
                toast({
                    title: 'Éxito',
                    description: 'Usuario eliminado correctamente',
                    status: 'success',
                    duration: 3000,
                    isClosable: true,
                });
                fetchUsers();
            } catch (error) {
                toast({
                    title: 'Error',
                    description: error.message || 'No se pudo eliminar el usuario',
                    status: 'error',
                    duration: 5000,
                    isClosable: true,
                });
            }
        }
    };

    const handleEdit = (user) => {
        setSelectedUser(user);
        onOpen();
    };

    const handleSave = async (e) => {
        e.preventDefault();
        try {
            await adminService.updateUser(selectedUser.id, {
                nombre_usuario: selectedUser.username,
                correo: selectedUser.email,
                creditos: selectedUser.credits
            });
            toast({
                title: 'Éxito',
                description: 'Usuario actualizado correctamente',
                status: 'success',
                duration: 3000,
                isClosable: true,
            });
            onClose();
            fetchUsers();
        } catch (error) {
            toast({
                title: 'Error',
                description: 'No se pudo actualizar el usuario',
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

    const getSortedUsers = () => {
        if (!sortConfig.key) return users;

        return [...users].sort((a, b) => {
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

    return (
        <Box p={5}>
            <InputGroup mb={4}>
                <InputLeftElement pointerEvents="none">
                    🔍
                </InputLeftElement>
                <Input
                    placeholder="Buscar por nombre de usuario o email..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
            </InputGroup>

            <Table variant="simple">
                <Thead>
                    <Tr>
                        <Th>Foto</Th>
                        <Th 
                            cursor="pointer" 
                            onClick={() => handleSort('username')}
                            _hover={{ bg: 'gray.100' }}
                        >
                            Nombre{getSortIndicator('username')}
                        </Th>
                        <Th 
                            cursor="pointer" 
                            onClick={() => handleSort('email')}
                            _hover={{ bg: 'gray.100' }}
                        >
                            Email{getSortIndicator('email')}
                        </Th>
                        <Th 
                            cursor="pointer" 
                            onClick={() => handleSort('credits')}
                            _hover={{ bg: 'gray.100' }}
                        >
                            Créditos{getSortIndicator('credits')}
                        </Th>
                        <Th>Acciones</Th>
                    </Tr>
                </Thead>
                <Tbody>
                    {getSortedUsers().map((user) => (
                        <Tr key={user.id}>
                            <Td>
                                <Image
                                    src={user.foto_perfil || 'https://via.placeholder.com/50'}
                                    alt={user.username}
                                    boxSize="50px"
                                    objectFit="cover"
                                    borderRadius="full"
                                />
                            </Td>
                            <Td>{user.username}</Td>
                            <Td>{user.email}</Td>
                            <Td>{user.credits}</Td>
                            <Td>
                                <Button
                                    colorScheme="blue"
                                    size="sm"
                                    mr={2}
                                    onClick={() => handleEdit(user)}
                                >
                                    Editar
                                </Button>
                                <Button
                                    colorScheme="red"
                                    size="sm"
                                    onClick={() => handleDelete(user.id)}
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
                    <ModalHeader>Editar Usuario</ModalHeader>
                    <ModalCloseButton />
                    <ModalBody>
                        <form onSubmit={handleSave}>
                            <VStack spacing={4}>
                                <FormControl>
                                    <FormLabel>Nombre de Usuario</FormLabel>
                                    <Input
                                        value={selectedUser?.username || ''}
                                        onChange={(e) =>
                                            setSelectedUser({
                                                ...selectedUser,
                                                username: e.target.value,
                                            })
                                        }
                                    />
                                </FormControl>
                                <FormControl>
                                    <FormLabel>Email</FormLabel>
                                    <Input
                                        value={selectedUser?.email || ''}
                                        onChange={(e) =>
                                            setSelectedUser({
                                                ...selectedUser,
                                                email: e.target.value,
                                            })
                                        }
                                    />
                                </FormControl>
                                <FormControl>
                                    <FormLabel>Créditos</FormLabel>
                                    <Input
                                        type="number"
                                        value={selectedUser?.credits || 0}
                                        onChange={(e) =>
                                            setSelectedUser({
                                                ...selectedUser,
                                                credits: parseInt(e.target.value),
                                            })
                                        }
                                    />
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

export default UserManagement; 