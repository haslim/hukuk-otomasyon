import { AppLayout } from './layouts/AppLayout';
import { AppRoutes } from './router/AppRoutes';
import { LoginGate } from './components/LoginGate';

const App = () => (
  <LoginGate>
    <AppLayout>
      <AppRoutes />
    </AppLayout>
  </LoginGate>
);

export default App;
