import { useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { ProfileApi } from '../api/modules/profile';

interface Props {
  children: React.ReactNode;
}

export const ProfileLoader = ({ children }: Props) => {
  const { token, user, setUser } = useAuth();

  useEffect(() => {
    // Only load profile if we have a token but user data might be incomplete (no roles)
    if (token && user && (!user.roles || user.roles.length === 0)) {
      const loadProfile = async () => {
        try {
          const profileData = await ProfileApi.getProfile();
          console.log('Profile data loaded:', profileData);
          setUser(profileData);
        } catch (error) {
          console.error('Failed to load profile data:', error);
        }
      };

      loadProfile();
    }
  }, [token, user, setUser]);

  return <>{children}</>;
};