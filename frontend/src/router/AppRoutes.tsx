import { Routes, Route } from 'react-router-dom';
import { DashboardPage } from '../pages/Dashboard/DashboardPage';
import { ClientsPage } from '../pages/Clients/ClientsPage';
import { CasesPage } from '../pages/Cases/CasesPage';
import { CaseDetailPage } from '../pages/Cases/CaseDetailPage';
import { WorkflowPage } from '../pages/Workflow/WorkflowPage';
import { DocumentsPage } from '../pages/Documents/DocumentsPage';
import { FinancePage } from '../pages/Finance/FinancePage';
import { CashAccountPage } from '../pages/Finance/CashAccountPage';
import { CalendarPage } from '../pages/Calendar/CalendarPage';
import { UserManagementPage } from '../pages/Users/UserManagementPage';
import { RoleManagementPage } from '../pages/Users/RoleManagementPage';
import { NotificationsPage } from '../pages/Notifications/NotificationsPage';
import { SearchPage } from '../pages/Search/SearchPage';

export const AppRoutes = () => (
  <Routes>
    <Route path="/" element={<DashboardPage />} />
    <Route path="/clients" element={<ClientsPage />} />
    <Route path="/cases" element={<CasesPage />} />
    <Route path="/cases/:id" element={<CaseDetailPage />} />
    <Route path="/workflow" element={<WorkflowPage />} />
    <Route path="/documents" element={<DocumentsPage />} />
    <Route path="/finance" element={<FinancePage />} />
    <Route path="/finance/cash" element={<CashAccountPage />} />
    <Route path="/calendar" element={<CalendarPage />} />
    <Route path="/users" element={<UserManagementPage />} />
    <Route path="/users/roles" element={<RoleManagementPage />} />
    <Route path="/notifications" element={<NotificationsPage />} />
    <Route path="/search" element={<SearchPage />} />
  </Routes>
);
