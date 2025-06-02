import { useState, useEffect } from 'react';
import './CookieConsent.css';

const CookieConsent = () => {
    const [showConsent, setShowConsent] = useState(false);
    const [isDarkMode, setIsDarkMode] = useState(false);

    useEffect(() => {
        const consent = localStorage.getItem('cookieConsent');
        const theme = localStorage.getItem('theme');
        setIsDarkMode(theme === 'dark');
        
        if (!consent) {
            setShowConsent(true);
        }
    }, []);

    const handleAccept = () => {
        localStorage.setItem('cookieConsent', 'true');
        setShowConsent(false);
    };

    if (!showConsent) return null;

    return (
        <div className={`cookie-consent ${isDarkMode ? 'dark-mode' : ''}`}>
            <div className="cookie-content">
                <p className="cookie-text">
                    Esta web utiliza cookies para mejorar tu experiencia de navegación. 
                    Al continuar navegando, aceptas nuestra política de cookies y términos de uso.
                </p>
                <button onClick={handleAccept} className="cookie-button">
                    Aceptar
                </button>
            </div>
        </div>
    );
};

export default CookieConsent; 