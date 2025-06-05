import React, { useState, useRef, useEffect } from 'react';
import { useNotifications } from '../../contexts/NotificationContext';
import NotificationList from './NotificationList';
import './NotificationBell.css';

const NotificationBell = () => {
    const { unreadCount, loading, error } = useNotifications();
    const [showDropdown, setShowDropdown] = useState(false);
    const dropdownRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setShowDropdown(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const toggleDropdown = () => {
        setShowDropdown(!showDropdown);
    };

    return (
        <div className="notification-bell" ref={dropdownRef}>
            <button
                className="notification-bell-button"
                onClick={toggleDropdown}
                aria-label="Notificaciones"
                disabled={loading}
            >
                <i className="bi bi-bell"></i>
                {unreadCount > 0 && (
                    <span className="notification-badge" style={{
                        position: 'absolute',
                        top: '-5px',
                        right: '-5px',
                        backgroundColor: '#dc3545',
                        color: 'white',
                        borderRadius: '50%',
                        padding: '2px 6px',
                        fontSize: '0.75rem',
                        minWidth: '18px',
                        height: '18px',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        zIndex: 1000,
                        boxShadow: '0 2px 4px rgba(0,0,0,0.2)'
                    }}>
                        {unreadCount}
                    </span>
                )}
                {loading && (
                    <span className="notification-loading">
                        <i className="bi bi-arrow-repeat"></i>
                    </span>
                )}
            </button>
            {showDropdown && <NotificationList onClose={() => setShowDropdown(false)} />}
            {error && (
                <div className="notification-error">
                    <i className="bi bi-exclamation-circle"></i>
                    <span>{error}</span>
                </div>
            )}
        </div>
    );
};

export default NotificationBell; 