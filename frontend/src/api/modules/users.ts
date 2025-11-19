import { apiClient } from '../client';

export interface User {
  id: string;
  fullName: string;
  email: string;
  status: 'active' | 'inactive';
  roles: string[];
  createdAt?: string;
  updatedAt?: string;
}

export interface CreateUserRequest {
  fullName: string;
  email: string;
  password: string;
  roles: string[];
  status: 'active' | 'inactive';
}

export interface UpdateUserRequest {
  fullName?: string;
  email?: string;
  password?: string;
  roles?: string[];
  status?: 'active' | 'inactive';
}

export interface Permission {
  id: string;
  name: string;
  code: string;
  description: string;
  enabled: boolean;
}

export interface Role {
  id: string;
  name: string;
  permissions: Permission[];
}

export interface UpdateRolePermissionsRequest {
  roleId: string;
  permissions: {
    id: string;
    enabled: boolean;
  }[];
}

export const UsersApi = {
  getUsers: () => apiClient.get('/users').then((res: any) => res.data),
  
  getUserById: (id: string) => apiClient.get(`/users/${id}`).then((res: any) => res.data),
  
  createUser: (userData: CreateUserRequest) =>
    apiClient.post('/users', userData).then((res: any) => res.data),
  
  updateUser: (id: string, userData: UpdateUserRequest) =>
    apiClient.put(`/users/${id}`, userData).then((res: any) => res.data),
  
  deleteUser: (id: string) =>
    apiClient.delete(`/users/${id}`).then((res: any) => res.data),
  
  toggleUserStatus: (id: string) =>
    apiClient.patch(`/users/${id}/toggle-status`).then((res: any) => res.data),
};

export const RolesApi = {
  getRoles: () =>
    apiClient.get('/roles').then((res: any) => {
      const rawData = res.data;
      const raw: any[] = Array.isArray(rawData)
        ? rawData
        : Array.isArray(rawData?.roles)
          ? rawData.roles
          : [];

      return raw
        .filter((role: any) => role && typeof role === 'object')
        .map(
          (role): Role => ({
            id: String(role.id),
            name: role.name ?? '',
            permissions: (Array.isArray(role.permissions) ? role.permissions : []).map(
              (perm: any, index: number): Permission => ({
                id: String(perm.id ?? `${role.id}-${index}`),
                name: perm.name ?? perm.key ?? '',
                code: perm.key ?? perm.code ?? '',
                description: perm.description ?? '',
                enabled: true,
              }),
            ),
          }),
        );
    }),
  
  getRoleById: (id: string) => apiClient.get(`/roles/${id}`).then((res: any) => res.data),
  
  createRole: (roleData: Omit<Role, 'id'>) =>
    apiClient.post('/roles', roleData).then((res: any) => res.data),
  
  updateRolePermissions: (roleId: string, permissions: UpdateRolePermissionsRequest) =>
    apiClient.put(`/roles/${roleId}/permissions`, permissions).then((res: any) => res.data),
  
  getPermissions: () => apiClient.get('/permissions').then((res: any) => res.data),
};

export interface SettingsData {
  notifications: {
    emailNotifications: boolean;
    pushNotifications: boolean;
    caseUpdates: boolean;
    taskReminders: boolean;
  };
  appearance: {
    theme: string;
    language: string;
    timezone: string;
  };
  privacy: {
    showProfileToOthers: boolean;
    showOnlineStatus: boolean;
  };
}

export const SettingsApi = {
  getSettings: () => apiClient.get('/settings').then((res: any) => res.data),
  
  updateSettings: (settings: SettingsData) =>
    apiClient.put('/settings', { settings }).then((res: any) => res.data),
};
