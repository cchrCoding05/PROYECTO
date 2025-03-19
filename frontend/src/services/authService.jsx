import { fetchApi } from "./api";

export const login = async (credentials) => {
  try {
    const response = await fetchApi("/api/user/login", {
      method: "POST",
      body: JSON.stringify(credentials),
    });

    return { success: true, user: response };
  } catch (error) {
    console.error("Login error:", error);
    return {
      success: false,
      message: typeof error === "string" ? error : "Error de inicio de sesión",
    };
  }
};

export const register = async (userData) => {
  try {
    const response = await fetchApi("/api/user/register", {
      method: "POST",
      body: JSON.stringify(userData),
    });

    return { success: true, user: response };
  } catch (error) {
    console.error("Registration error:", error);
    return {
      success: false,
      message: typeof error === "string" ? error : "Error en el registro",
    };
  }
};

export const logout = async () => {
  try {
    await fetchApi("/api/user/logout", {
      method: "POST",
    });

    return { success: true };
  } catch (error) {
    console.error("Logout error:", error);
    return { success: false, message: "Error al cerrar sesión" };
  }
};

export const getCurrentUser = async () => {
  try {
    const user = await fetchApi("/api/user/session");
    return user;
  } catch (error) {
    // Not logged in, return null instead of throwing
    console.log("Not logged in or session expired");
    return null;
  }
};
