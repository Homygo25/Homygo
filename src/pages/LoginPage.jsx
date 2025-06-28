
import React, { useContext, useState } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '@/contexts/AuthContext.jsx';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { motion } from "framer-motion";
import { Eye, EyeOff, Loader2, Chrome } from 'lucide-react';

const LoginPage = () => {
  const auth = useContext(AuthContext);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);

  const handleLogin = async (e) => {
    e.preventDefault();
    await auth.login(email, password);
  };

  const handleGoogleLogin = async () => {
    await auth.signInWithGoogle();
  };

  return (
    <div className="min-h-screen w-full flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-200 p-4">
      <motion.div 
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="w-full max-w-md p-8 space-y-6 bg-white rounded-2xl shadow-2xl"
      >
        <div className="text-center">
          <Link to="/">
            <img alt="Homygo Logo" className="w-32 h-auto mx-auto mb-4" src="/logo.png" />
          </Link>
          <h1 className="text-3xl font-bold text-gray-800">Welcome Back!</h1>
          <p className="text-gray-500">Sign in to manage your properties.</p>
        </div>
        <form onSubmit={handleLogin} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="email" className="text-gray-600">Email</Label>
            <Input 
              id="email" 
              type="email" 
              placeholder="name@example.com" 
              required 
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              disabled={auth.loading}
            />
          </div>
          <div className="relative space-y-2">
            <Label htmlFor="password" className="text-gray-600">Password</Label>
            <Input 
              id="password" 
              type={showPassword ? "text" : "password"} 
              required 
              placeholder="••••••••"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              disabled={auth.loading}
              className="pr-10"
            />
            <button 
              type="button" 
              onClick={() => setShowPassword(!showPassword)} 
              className="absolute right-3 top-8 text-gray-400 hover:text-gray-600 transition-colors"
              aria-label={showPassword ? "Hide password" : "Show password"}
            >
              {showPassword ? <EyeOff size={20} /> : <Eye size={20} />}
            </button>
          </div>
          <div className="flex items-center justify-end">
            <Link to="/forgot-password" className="text-sm font-medium text-primary hover:underline">
              Forgot password?
            </Link>
          </div>
          <Button type="submit" className="w-full !mt-6" disabled={auth.loading}>
            {auth.loading ? <Loader2 className="w-5 h-5 mr-2 animate-spin" /> : 'Sign In'}
          </Button>
        </form>

        {/* Google Sign In */}
        <div className="relative my-6">
          <div className="absolute inset-0 flex items-center">
            <span className="w-full border-t border-gray-300" />
          </div>
          <div className="relative flex justify-center text-xs uppercase">
            <span className="bg-white px-2 text-gray-500">Or continue with</span>
          </div>
        </div>
        
        <Button 
          variant="outline" 
          className="w-full border-gray-300 hover:bg-gray-50 text-gray-700" 
          onClick={handleGoogleLogin} 
          disabled={auth.loading}
        >
          {auth.loading ? <Loader2 className="w-5 h-5 mr-2 animate-spin" /> : <Chrome className="w-5 h-5 mr-2" />}
          Sign in with Google
        </Button>

        <p className="text-center text-sm text-gray-500 mt-6">
          Don't have an account?{' '}
          <Link to="/signup" className="font-medium text-primary hover:underline">
            Sign up
          </Link>
        </p>
      </motion.div>
    </div>
  );
};

export default LoginPage;
