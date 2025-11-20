import { Routes, Route } from 'react-router-dom';
import { DashboardPage } from '../pages/Dashboard/DashboardPage';
import { ClientsPage } from '../pages/Clients/ClientsPage';
import { CasesPage } from '../pages/Cases/CasesPage';
import { CaseDetailPage } from '../pages/Cases/CaseDetailPage';
import { WorkflowPage } from '../pages/Workflow/WorkflowPage';
import { DocumentsPage } from '../pages/Documents/DocumentsPage';
import { DocumentTemplatesPage } from '../pages/Documents/DocumentTemplatesPage';
import { FinancePage } from '../pages/Finance/FinancePage';
import { CashAccountPage } from '../pages/Finance/CashAccountPage';
import { CalendarPage } from '../pages/Calendar/CalendarPage';
import { UserManagementPage } from '../pages/Users/UserManagementPage';
import { RoleManagementPage } from '../pages/Users/RoleManagementPage';
import { NotificationsPage } from '../pages/Notifications/NotificationsPage';
import { SearchPage } from '../pages/Search/SearchPage';
import { MediationListPage } from '../pages/mediation/MediationListPage';
import { MediationDetailPage } from '../pages/mediation/MediationDetailPage';
import { MediationNewPage } from '../pages/mediation/MediationNewPage';
import { ProfilePage } from '../pages/Profile/ProfilePage';
import { MenuManagementPage } from '../pages/MenuManagementPage';
import { SettingsPage } from '../pages/SettingsPage';
import ArbitrationListPage from '../pages/Arbitration/ArbitrationListPage';
import ArbitrationDashboardPage from '../pages/Arbitration/ArbitrationDashboardPage';
import MediationFeeCalculatorPage from '../pages/MediationFeeCalculatorPage';
import { InvoicesPage } from '../pages/Invoices/InvoicesPage';

export const AppRoutes = () => (
  <Routes>
    <Route path="/" element={<DashboardPage />} />
    <Route path="/clients" element={<ClientsPage />} />
    <Route path="/cases" element={<CasesPage />} />
    <Route path="/cases/:id" element={<CaseDetailPage />} />
    <Route path="/workflow" element={<WorkflowPage />} />
    <Route path="/documents" element={<DocumentsPage />} />
    <Route path="/documents/templates" element={<DocumentTemplatesPage />} />
    <Route path="/finance" element={<FinancePage />} />
    <Route path="/finance/cash" element={<CashAccountPage />} />
    <Route path="/calendar" element={<CalendarPage />} />
    <Route path="/users" element={<UserManagementPage />} />
    <Route path="/users/roles" element={<RoleManagementPage />} />
    <Route path="/notifications" element={<NotificationsPage />} />
    <Route path="/search" element={<SearchPage />} />
    <Route path="/mediation" element={<MediationListPage />} />
    <Route path="/mediation/list" element={<MediationListPage />} />
    <Route path="/mediation/new" element={<MediationNewPage />} />
    <Route path="/mediation/:id" element={<MediationDetailPage />} />
    <Route path="/profile" element={<ProfilePage />} />
    <Route path="/menu-management" element={<MenuManagementPage />} />
    <Route path="/users/settings" element={<SettingsPage />} />
    <Route path="/arbitration" element={<ArbitrationListPage />} />
    <Route path="/arbitration/dashboard" element={<ArbitrationDashboardPage />} />
    <Route path="/mediation/fee-calculator" element={<MediationFeeCalculatorPage />} />
    <Route path="/invoices" element={<InvoicesPage />} />
  </Routes>
);
