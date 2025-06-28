import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useAuth } from '@/contexts/AuthContext.jsx';
import { useNavigate } from 'react-router-dom';
import { UserCircle, Briefcase, DollarSign, Home, LogOut, Settings, LifeBuoy, Edit3, Shield, ListChecks, Award } from 'lucide-react';
import { toast } from '@/components/ui/use-toast';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog"

const OwnerProfilePage = () => {
  const { user, userProfile, userRole, logout } = useAuth();
  const navigate = useNavigate();
  const [showLogoutConfirm, setShowLogoutConfirm] = useState(false);

  const handleLogout = async () => {
    await logout({ navigateTo: '/' });
    toast({ title: "Logged Out", description: "You have been successfully logged out." });
  };

  const getInitials = (name) => {
    if (!name) return 'O';
    const names = name.split(' ');
    if (names.length > 1) {
      return `${names[0][0]}${names[names.length - 1][0]}`.toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  };

  const handleFeatureClick = (featureName, path = null) => {
    if (path) {
      navigate(path);
    } else {
      toast({
        title: `🚧 ${featureName} Coming Soon!`,
        description: "This feature isn't implemented yet—but we're working on it! 🚀",
        variant: 'default'
      });
    }
  };
  
  const profileItems = [
    { label: "My Listings", icon: ListChecks, action: () => navigate('/owner/dashboard') },
    { label: "Bookings", icon: Briefcase, action: () => handleFeatureClick("Bookings Management") },
    { label: "Earnings Summary", icon: DollarSign, action: () => handleFeatureClick("Earnings Summary") },
  ];

  const footerOptions = [
    { label: "Account Settings", icon: Settings, action: () => navigate('/account-settings'), arrow: true },
    { label: "Referral Program", icon: Award, action: () => handleFeatureClick("Invite landlords to earn ₱500! Refer new property owners to Homygo. Once they list their first property and get a booking, you both earn a ₱500 bonus. T&Cs apply."), arrow: true },
    { label: "Get help", icon: LifeBuoy, action: () => handleFeatureClick("Help Center"), arrow: true },
    { label: "Logout", icon: LogOut, action: () => setShowLogoutConfirm(true), arrow: false, color: "text-destructive" },
  ];

  if (!user || !userProfile) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <p>Loading profile...</p>
      </div>
    );
  }
  
  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.3 }}
      className="min-h-screen bg-muted/30 pb-24 sm:pb-16"
    >
      <div className="container mx-auto max-w-2xl py-6 sm:py-8 px-4">
        <header className="mb-6 sm:mb-8">
          <h1 className="text-2xl sm:text-3xl font-bold text-foreground">Owner Profile</h1>
        </header>

        <section className="bg-card p-6 rounded-xl shadow-lg mb-6 sm:mb-8 border border-border/30">
          <div className="flex items-center space-x-4">
            <Avatar className="h-20 w-20 sm:h-24 sm:w-24 border-2 border-primary/50">
              <AvatarImage src={user.user_metadata?.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(userProfile?.name || user.email)}&background=10B981&color=fff&bold=true&size=128`} alt={userProfile?.name || 'User'} />
              <AvatarFallback className="text-2xl sm:text-3xl bg-primary/20 text-primary font-semibold">
                {getInitials(userProfile?.name || user.email)}
              </AvatarFallback>
            </Avatar>
            <div>
              <h2 className="text-xl sm:text-2xl font-semibold text-foreground">{userProfile?.name || 'Homygo Owner'}</h2>
              <div className="flex items-center">
                 <p className="text-sm sm:text-base text-muted-foreground capitalize mr-2">{userRole} Account</p>
                 <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent text-accent-foreground">
                    Owner
                 </span>
              </div>
              <Button variant="link" className="p-0 h-auto text-xs text-primary hover:underline" onClick={() => handleFeatureClick("Edit Profile")}>
                <Edit3 className="w-3 h-3 mr-1"/> Edit profile
              </Button>
            </div>
          </div>
        </section>

        <section className="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
          {profileItems.map((item) => (
            <motion.div 
              key={item.label}
              whileHover={{ scale: 1.03 }}
              onClick={item.action}
              className="bg-card p-5 rounded-xl shadow-md cursor-pointer border border-border/30 hover:border-primary/50 transition-all text-center"
            >
              <item.icon className="w-10 h-10 text-primary mx-auto mb-2"/>
              <h3 className="font-semibold text-foreground text-sm sm:text-base">{item.label}</h3>
            </motion.div>
          ))}
        </section>
        
        <motion.div 
            whileHover={{ y: -2 }}
            onClick={() => navigate('/owner/properties/create')}
            className="bg-gradient-to-r from-primary to-accent text-primary-foreground p-5 rounded-xl shadow-lg cursor-pointer mb-6 sm:mb-8 transition-all flex items-center space-x-4 hover:shadow-xl"
        >
            <div className="bg-white/20 p-3 rounded-lg">
                <Home className="h-6 w-6 text-white" />
            </div>
            <div>
                <h3 className="font-semibold text-base sm:text-lg">Add New Property</h3>
                <p className="text-xs opacity-90">Expand your portfolio and reach more guests on Homygo.</p>
            </div>
        </motion.div>

        <section className="bg-card rounded-xl shadow-lg border border-border/30 overflow-hidden">
          {footerOptions.map((item, index) => (
            <div
              key={item.label}
              onClick={item.action}
              className={`flex items-center justify-between p-4 sm:p-5 cursor-pointer hover:bg-muted/50 transition-colors ${item.color || 'text-foreground'} ${index < footerOptions.length - 1 ? 'border-b border-border/30' : ''}`}
            >
              <div className="flex items-center space-x-3 sm:space-x-4">
                <item.icon className={`h-5 w-5 sm:h-6 sm:w-6 ${item.color || 'text-primary'}`} />
                <span className="text-sm sm:text-base font-medium">{item.label}</span>
              </div>
              {item.arrow && <ChevronRight className="h-4 w-4 sm:h-5 sm:w-5 text-muted-foreground" />}
            </div>
          ))}
        </section>
        
        <AlertDialog open={showLogoutConfirm} onOpenChange={setShowLogoutConfirm}>
          <AlertDialogContent className="bg-card border-border">
            <AlertDialogHeader>
              <AlertDialogTitle className="text-foreground">Are you sure you want to logout?</AlertDialogTitle>
              <AlertDialogDescription className="text-muted-foreground">
                You will be returned to the home page. You can always log back in.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel className="hover:bg-muted/50 border-border text-foreground min-h-[44px]">Cancel</AlertDialogCancel>
              <AlertDialogAction onClick={handleLogout} className="bg-destructive hover:bg-destructive/90 text-destructive-foreground min-h-[44px]">
                Logout
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>

      </div>
    </motion.div>
  );
};

const ChevronRight = (props) => (
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" {...props}>
    <path fillRule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clipRule="evenodd" />
  </svg>
);

export default OwnerProfilePage;