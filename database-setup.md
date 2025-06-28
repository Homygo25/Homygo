# Database Setup & Test Credentials

## Test User Credentials

### Owner Account
- **Email**: `owner@homygo.test`
- **Password**: `owner123`
- **Role**: owner
- **Name**: John Smith (Property Owner)

### Guest Account
- **Email**: `guest@homygo.test`
- **Password**: `guest123`
- **Role**: guest
- **Name**: Jane Doe (Guest User)

## Database Tables Structure

The following tables should exist in your Supabase database:

### 1. `profiles` table
```sql
CREATE TABLE profiles (
  id uuid REFERENCES auth.users ON DELETE CASCADE,
  full_name text,
  role text CHECK (role IN ('guest', 'owner', 'admin')),
  avatar_url text,
  phone text,
  created_at timestamp with time zone DEFAULT timezone('utc'::text, now()) NOT NULL,
  updated_at timestamp with time zone DEFAULT timezone('utc'::text, now()) NOT NULL,
  PRIMARY KEY (id)
);
```

### 2. `properties` table
```sql
CREATE TABLE properties (
  id uuid DEFAULT gen_random_uuid() PRIMARY KEY,
  user_id uuid REFERENCES auth.users(id) ON DELETE CASCADE,
  title text NOT NULL,
  description text,
  property_type text,
  price_per_night decimal(10,2),
  address text,
  city text,
  state text,
  country text DEFAULT 'Philippines',
  latitude decimal(10,8),
  longitude decimal(11,8),
  bedrooms integer,
  bathrooms integer,
  max_guests integer,
  amenities text[],
  images text[],
  status text DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'pending')),
  created_at timestamp with time zone DEFAULT timezone('utc'::text, now()) NOT NULL,
  updated_at timestamp with time zone DEFAULT timezone('utc'::text, now()) NOT NULL
);
```

### 3. `bookings` table
```sql
CREATE TABLE bookings (
  id uuid DEFAULT gen_random_uuid() PRIMARY KEY,
  property_id uuid REFERENCES properties(id) ON DELETE CASCADE,
  guest_id uuid REFERENCES auth.users(id) ON DELETE CASCADE,
  start_date date NOT NULL,
  end_date date NOT NULL,
  total_price decimal(10,2),
  status text DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'cancelled', 'completed')),
  guest_count integer DEFAULT 1,
  special_requests text,
  created_at timestamp with time zone DEFAULT timezone('utc'::text, now()) NOT NULL,
  updated_at timestamp with time zone DEFAULT timezone('utc'::text, now()) NOT NULL
);
```

### 4. `property_availability` table
```sql
CREATE TABLE property_availability (
  id uuid DEFAULT gen_random_uuid() PRIMARY KEY,
  property_id uuid REFERENCES properties(id) ON DELETE CASCADE,
  date date NOT NULL,
  is_available boolean DEFAULT true,
  custom_price decimal(10,2),
  created_at timestamp with time zone DEFAULT timezone('utc'::text, now()) NOT NULL,
  UNIQUE(property_id, date)
);
```

## Row Level Security (RLS) Policies

Enable RLS on all tables and add the following policies:

### Profiles policies
```sql
ALTER TABLE profiles ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can view own profile" ON profiles FOR SELECT USING (auth.uid() = id);
CREATE POLICY "Users can update own profile" ON profiles FOR UPDATE USING (auth.uid() = id);
CREATE POLICY "Users can insert own profile" ON profiles FOR INSERT WITH CHECK (auth.uid() = id);
```

### Properties policies
```sql
ALTER TABLE properties ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Anyone can view active properties" ON properties FOR SELECT USING (status = 'active');
CREATE POLICY "Property owners can manage their properties" ON properties FOR ALL USING (auth.uid() = user_id);
```

### Bookings policies
```sql
ALTER TABLE bookings ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can view their bookings" ON bookings FOR SELECT USING (auth.uid() = guest_id OR auth.uid() IN (SELECT user_id FROM properties WHERE id = property_id));
CREATE POLICY "Guests can create bookings" ON bookings FOR INSERT WITH CHECK (auth.uid() = guest_id);
CREATE POLICY "Property owners and guests can update bookings" ON bookings FOR UPDATE USING (auth.uid() = guest_id OR auth.uid() IN (SELECT user_id FROM properties WHERE id = property_id));
```

## Creating Test Users

You can create these test users by:

1. **Using the SignUp page**: Go to `/signup` and create accounts with the above credentials
2. **Using Supabase Auth UI**: In your Supabase dashboard
3. **Using the SQL below** in your Supabase SQL editor:

```sql
-- Note: You'll need to use the signup page or Supabase auth to create actual users
-- This is just for reference of what the profile data should look like

-- After users are created via signup, you can update their profiles:
UPDATE profiles SET 
  full_name = 'John Smith (Property Owner)',
  role = 'owner'
WHERE id = (SELECT id FROM auth.users WHERE email = 'owner@homygo.test');

UPDATE profiles SET 
  full_name = 'Jane Doe (Guest User)',
  role = 'guest'  
WHERE id = (SELECT id FROM auth.users WHERE email = 'guest@homygo.test');
```

## Sample Property Data

After creating the owner account, you can add sample properties:

```sql
-- Insert sample property (replace 'owner-user-id' with actual owner's UUID)
INSERT INTO properties (user_id, title, description, property_type, price_per_night, address, city, state, bedrooms, bathrooms, max_guests, amenities, images) VALUES 
('owner-user-id', 'Cozy Downtown Condo', 'Modern 2-bedroom condo in the heart of Cagayan de Oro', 'Condominium', 1500.00, '123 Velez Street', 'Cagayan de Oro', 'Misamis Oriental', 2, 1, 4, 
 ARRAY['WiFi', 'Air Conditioning', 'Kitchen', 'TV', 'Parking'], 
 ARRAY['https://images.unsplash.com/photo-1560448204-e02f11c3d0e2', 'https://images.unsplash.com/photo-1560449752-18b87cd8b1fd']);
```

## Quick Setup Instructions

1. Copy the SQL statements above to your Supabase SQL editor
2. Run them to create the database structure
3. Go to your app's signup page and create the test accounts
4. Use the login credentials provided above
5. For the owner account, you can add properties through the dashboard
