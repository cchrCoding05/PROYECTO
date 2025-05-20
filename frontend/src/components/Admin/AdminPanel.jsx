import React from 'react';
import { Box, Tabs, TabList, TabPanels, Tab, TabPanel } from '@chakra-ui/react';
import UserManagement from './UserManagement';
import ProductManagement from './ProductManagement';

const AdminPanel = () => {
    return (
        <Box p={5}>
            <Tabs>
                <TabList>
                    <Tab>Usuarios</Tab>
                    <Tab>Productos</Tab>
                </TabList>

                <TabPanels>
                    <TabPanel>
                        <UserManagement />
                    </TabPanel>
                    <TabPanel>
                        <ProductManagement />
                    </TabPanel>
                </TabPanels>
            </Tabs>
        </Box>
    );
};

export default AdminPanel; 