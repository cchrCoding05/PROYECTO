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
                    <span className="notification-badge">{unreadCount}</span>
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