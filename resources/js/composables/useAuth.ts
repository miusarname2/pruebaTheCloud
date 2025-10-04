import { ref } from 'vue';
import api from '@/lib/api';

interface User {
    id: number;
    name: string;
    email: string;
}

interface LoginResponse {
    access_token: string;
    token_type: string;
    user: User;
}

const user = ref<User | null>(null);
const token = ref<string | null>(localStorage.getItem('api_token'));

export function useAuth() {
    const login = async (email: string, password: string): Promise<void> => {
        try {
            const response = await api.post<LoginResponse>('/login', {
                email,
                password,
            });

            const { access_token, user: userData } = response.data;
            token.value = access_token;
            user.value = userData;
            localStorage.setItem('api_token', access_token);
        } catch (error) {
            throw error;
        }
    };

    const logout = async (): Promise<void> => {
        try {
            await api.post('/logout');
        } catch (error) {
            // Even if API call fails, clear local data
            console.error('Logout API error:', error);
        } finally {
            token.value = null;
            user.value = null;
            localStorage.removeItem('api_token');
        }
    };

    const isAuthenticated = (): boolean => {
        return !!token.value;
    };

    const getToken = (): string | null => {
        return token.value;
    };

    const getUser = (): User | null => {
        return user.value;
    };

    return {
        user,
        token,
        login,
        logout,
        isAuthenticated,
        getToken,
        getUser,
    };
}
