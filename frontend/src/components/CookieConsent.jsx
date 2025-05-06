import { useState, useEffect } from 'react';
import '../styles/CookieConsent.css';

const CookieConsent = () => {
    const [showConsent, setShowConsent] = useState(false);

    useEffect(() => {
        const consent = localStorage.getItem('cookieConsent');
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
        <div className="cookie-consent">
            <div className="cookie-content">
                <p>
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