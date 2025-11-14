import { apiClient } from '../client';
import { AuthUser } from '../../context/AuthContext';

export interface UpdateProfilePayload {
  name?: string;
  title?: string;
  avatarUrl?: string;
  email?: string;
}

export const ProfileApi = {
  getProfile: () => apiClient.get('/profile').then((res: any) => res.data as AuthUser),
  updateProfile: (payload: UpdateProfilePayload) =>
    apiClient.put('/profile', payload).then((res: any) => res.data.user as AuthUser),
};

