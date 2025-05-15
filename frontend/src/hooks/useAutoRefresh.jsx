import { useEffect, useCallback, useRef } from 'react';

const useAutoRefresh = (callback, dependencies = [], interval = 20000) => {
  const isMounted = useRef(true);
  const isLoading = useRef(false);

  const refresh = useCallback(async () => {
    if (!isMounted.current || isLoading.current) {
      console.log('Saltando actualización - Componente desmontado o ya cargando');
      return;
    }

    try {
      console.log('Iniciando actualización automática...');
      isLoading.current = true;
      await callback();
      console.log('Actualización completada con éxito');
    } catch (error) {
      console.error('Error durante la actualización automática:', error);
    } finally {
      if (isMounted.current) {
        isLoading.current = false;
      }
    }
  }, [callback]);

  useEffect(() => {
    console.log('Configurando actualización automática...');
    isMounted.current = true;
    isLoading.current = false;

    // Ejecutar inmediatamente al montar
    refresh();

    // Configurar el intervalo solo si el componente está montado
    const intervalId = setInterval(() => {
      if (isMounted.current) {
        refresh();
      }
    }, interval);

    // Limpiar el intervalo al desmontar
    return () => {
      console.log('Desmontando componente, limpiando actualización automática');
      isMounted.current = false;
      clearInterval(intervalId);
    };
  }, [...dependencies, interval, refresh]);

  return refresh;
};

export default useAutoRefresh; 