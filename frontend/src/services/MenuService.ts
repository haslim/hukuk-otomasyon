import { apiClient } from '../api/client';

export interface MenuItem {
  id: string;
  path: string;
  label: string;
  icon: string;
  sortOrder: number;
  isActive: boolean;
  parentId?: string;
  children?: MenuItem[];
  createdAt?: string;
  updatedAt?: string;
}

export interface MenuPermission {
  id: string;
  path: string;
  label: string;
  icon: string;
  sortOrder: number;
  isVisible: boolean;
}

export interface RoleMenuPermission {
  menuItemId: string;
  isVisible: boolean;
}

export class MenuService {
  static async getMyMenu(): Promise<MenuItem[]> {
    const response = await apiClient.get('/menu/my');
    return response.data;
  }

  static async getAllMenuItems(): Promise<MenuItem[]> {
    const response = await apiClient.get('/menu');
    return response.data;
  }

  static async getMenuItems(): Promise<MenuItem[]> {
    const response = await apiClient.get('/menu');
    return response.data;
  }

  static async getMenuItem(id: string): Promise<MenuItem> {
    const response = await apiClient.get(`/menu/${id}`);
    return response.data;
  }

  static async createMenuItem(data: Omit<MenuItem, 'id' | 'createdAt' | 'updatedAt'>): Promise<MenuItem> {
    const response = await apiClient.post('/menu', data);
    return response.data;
  }

  static async updateMenuItem(id: string, data: Partial<MenuItem>): Promise<MenuItem> {
    const response = await apiClient.put(`/menu/${id}`, data);
    return response.data;
  }

  static async deleteMenuItem(id: string): Promise<void> {
    await apiClient.delete(`/menu/${id}`);
  }

  static async getRoleMenuPermissions(roleId: string): Promise<MenuPermission[]> {
    const response = await apiClient.get(`/menu/roles/${roleId}/permissions`);
    return response.data;
  }

  static async updateRoleMenuPermissions(roleId: string, permissions: RoleMenuPermission[]): Promise<MenuPermission[]> {
    const response = await apiClient.put(`/menu/roles/${roleId}/permissions`, {
      permissions
    });
    return response.data;
  }
}
