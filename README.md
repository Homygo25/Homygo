# Homygo - Property Rental Platform

A modern, mobile-friendly property rental platform built with React, Vite, Tailwind CSS, and Supabase.

## 🚀 Quick Start

### Test Login Credentials

#### Owner Account (Property Management)
- **Email**: `owner@homygo.test`
- **Password**: `owner123`
- **Features**: Add properties, manage bookings, view earnings dashboard

#### Guest Account (Property Browsing)
- **Email**: `guest@homygo.test`  
- **Password**: `guest123`
- **Features**: Browse properties, make bookings, view wishlist

### Running the Application

1. **Install dependencies**:
   ```bash
   npm install
   ```

2. **Start development server**:
   ```bash
   npm run dev
   ```

3. **Visit the application**:
   ```
   http://localhost:5173
   ```

### Setting Up Test Data (Optional)

If you need to create test users and sample properties:

```bash
node setup-test-data.js
```

## 📱 Mobile-Friendly Features

- Responsive design optimized for mobile devices
- Touch-friendly navigation and interactions
- Mobile-optimized forms and modals
- Swipe gestures for image galleries
- Bottom navigation for easy mobile access

## 🏠 Owner Dashboard Features

- **Property Management**: Add, edit, and delete property listings
- **Photo Upload**: Multiple image upload with drag-and-drop support
- **Calendar Management**: Set availability dates and custom pricing
- **Booking Management**: View and manage property bookings
- **Analytics**: Track earnings, occupancy rates, and performance metrics
- **Profile Management**: Update property owner information

## 👥 Guest Features

- **Property Browsing**: Search and filter available properties
- **Map View**: Interactive map showing property locations
- **Detailed Listings**: High-quality photos, amenities, and descriptions
- **Booking System**: Easy booking with date selection and guest count
- **User Account**: Save favorites and manage booking history
- **Reviews**: Read and leave property reviews

## 🛠 Tech Stack

- **Frontend**: React 18, Vite, Tailwind CSS
- **UI Components**: Radix UI, Lucide React Icons
- **Authentication**: Supabase Auth
- **Database**: Supabase (PostgreSQL)
- **Maps**: Mapbox GL JS
- **Animations**: Framer Motion
- **Routing**: React Router DOM

## 📁 Project Structure

```
src/
├── components/          # Reusable UI components
│   ├── ui/             # Base UI components (buttons, inputs, etc.)
│   ├── owner-dashboard/ # Owner-specific components
│   └── explore/        # Guest browsing components
├── pages/              # Main application pages
├── contexts/           # React context providers
├── hooks/              # Custom React hooks
├── lib/                # Utility libraries
└── data/               # Static data and configurations
```

## 🔐 Authentication System

The app uses Supabase for authentication with the following features:

- **Email/password signup and login**
- **Google OAuth integration** (Sign in/up with Google)
- **Role-based access** (owner/guest)
- **PIN-based quick access** for returning users
- **Password reset functionality**

### Google Authentication Setup
To enable Google login, you need to configure Google OAuth in your Supabase project. See `google-auth-setup.md` for detailed instructions.

### Login Options
- **Standard Login**: Email and password
- **Google Login**: One-click authentication with Google account
- **Role Selection**: Choose between Guest or Property Owner during signup

## 📊 Database Schema

Key database tables:
- `profiles` - User profile information
- `properties` - Property listings
- `bookings` - Reservation data
- `property_availability` - Calendar and pricing data

## 🚀 Deployment

The application is configured for easy deployment to:
- Vercel
- Netlify  
- Any static hosting service

Build for production:
```bash
npm run build
```

## 📞 Support

For issues or questions about the application, please check the existing documentation or create an issue in the project repository.
