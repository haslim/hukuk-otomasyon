import { AppLayout } from './layouts/AppLayout';
import { AppRoutes } from './router/AppRoutes';
import { LoginGate } from './components/LoginGate';
import { ProfileLoader } from './components/ProfileLoader';

const App = () => (
  <LoginGate>
    <ProfileLoader>
      <AppLayout>
        <AppRoutes />
      </AppLayout>
    </ProfileLoader>
  </LoginGate>
);

export default App;
