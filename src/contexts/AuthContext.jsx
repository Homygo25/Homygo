
import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { useToast } from '@/components/ui/use-toast';
import { supabase } from '@/lib/supabaseClient';
import { useAuthActions } from '@/contexts/authHooks/useAuthActions';
import { useAuthSession } from '@/contexts/authHooks/useAuthSession';
import { useAuthPin } from '@/contexts/authHooks/useAuthPin';
import { useAuthOnboarding } from '@/contexts/authHooks/useAuthOnboarding';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [loading, setLoading] = useState(true);
  const [isPinAccessRequired, setIsPinAccessRequired] = useState(false);
  const [isPinSetupComplete, setIsPinSetupComplete] = useState(false);
  const navigate = useNavigate();
  const { toast } = useToast();

  const {
    user,
    userProfile,
    userRole,
    isAuthenticated,
    setUser,
    setUserProfile,
    setUserRole,
    setIsAuthenticated,
    fetchUserProfileData,
  } = useAuthSession(setLoading);

  const processSession = useCallback(async (session) => {
    setLoading(true);
    if (session) {
      const profileData = await fetchUserProfileData(session.user.id);
      setUser(session.user);
      setUserProfile(profileData);
      setUserRole(profileData.role || 'guest');
      setIsAuthenticated(true);
    } else {
      setUser(null);
      setUserProfile(null);
      setUserRole('guest');
      setIsAuthenticated(false);
      setIsPinSetupComplete(false);
      setIsPinAccessRequired(false);
    }
    setLoading(false);
  }, [fetchUserProfileData, setUser, setUserProfile, setUserRole, setIsAuthenticated]);

  useEffect(() => {
    supabase.auth.getSession().then(({ data: { session } }) => {
      processSession(session);
    });

    const { data: { subscription } } = supabase.auth.onAuthStateChange(
      (_event, session) => {
        processSession(session);
      }
    );

    return () => subscription.unsubscribe();
  }, [processSession]);

  const authActions = useAuthActions(supabase, toast, setLoading, processSession, navigate);
  const pinActions = useAuthPin();
  const onboardingActions = useAuthOnboarding(user, userProfile, setUserProfile, setLoading, supabase, toast, navigate, userRole);

  const value = {
    user,
    userProfile,
    userRole,
    isAuthenticated,
    loading,
    isPinAccessRequired,
    isPinSetupComplete,
    setIsPinAccessRequired,
    setIsPinSetupComplete,
    ...authActions,
    ...pinActions,
    ...onboardingActions,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
