import { supabase } from './src/lib/supabaseClient.js';

// Test user credentials
const testUsers = [
  {
    email: 'owner@homygo.test',
    password: 'owner123',
    fullName: 'John Smith',
    role: 'owner'
  },
  {
    email: 'guest@homygo.test', 
    password: 'guest123',
    fullName: 'Jane Doe',
    role: 'guest'
  }
];

// Sample properties for the owner
const sampleProperties = [
  {
    title: 'Cozy Downtown Condo',
    description: 'Modern 2-bedroom condominium in the heart of Cagayan de Oro with stunning city views and premium amenities.',
    property_type: 'Condominium',
    price_per_night: 1500.00,
    address: '123 Velez Street, Poblacion',
    city: 'Cagayan de Oro',
    state: 'Misamis Oriental',
    country: 'Philippines',
    bedrooms: 2,
    bathrooms: 1,
    max_guests: 4,
    amenities: ['WiFi', 'Air Conditioning', 'Kitchen', 'TV', 'Parking', 'Pool', 'Gym'],
    images: [
      'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
      'https://images.unsplash.com/photo-1560449752-18b87cd8b1fd?w=800',
      'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800'
    ],
    status: 'active'
  },
  {
    title: 'Beachfront Villa',
    description: 'Luxurious beachfront villa with private beach access and infinity pool. Perfect for weekend getaways.',
    property_type: 'Villa',
    price_per_night: 3500.00,
    address: 'Macasandig Beach Road',
    city: 'Cagayan de Oro',
    state: 'Misamis Oriental', 
    country: 'Philippines',
    bedrooms: 4,
    bathrooms: 3,
    max_guests: 8,
    amenities: ['WiFi', 'Air Conditioning', 'Kitchen', 'TV', 'Parking', 'Private Beach', 'Pool', 'BBQ Area'],
    images: [
      'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800',
      'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800',
      'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800'
    ],
    status: 'active'
  },
  {
    title: 'Modern Studio Apartment',
    description: 'Compact but efficient studio apartment perfect for solo travelers or couples. Located near major shopping centers.',
    property_type: 'Apartment',
    price_per_night: 800.00,
    address: 'Divisoria Street, Cogon',
    city: 'Cagayan de Oro',
    state: 'Misamis Oriental',
    country: 'Philippines', 
    bedrooms: 1,
    bathrooms: 1,
    max_guests: 2,
    amenities: ['WiFi', 'Air Conditioning', 'Kitchenette', 'TV', 'Parking'],
    images: [
      'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800',
      'https://images.unsplash.com/photo-1560448075-bb485b067938?w=800'
    ],
    status: 'active'
  }
];

async function createTestUsers() {
  console.log('Creating test users...');
  
  for (const user of testUsers) {
    try {
      const { data, error } = await supabase.auth.signUp({
        email: user.email,
        password: user.password,
        options: {
          data: {
            full_name: user.fullName,
            role: user.role
          }
        }
      });
      
      if (error) {
        console.error(`Error creating user ${user.email}:`, error.message);
      } else {
        console.log(`✅ User created: ${user.email} (${user.role})`);
      }
    } catch (err) {
      console.error(`Exception creating user ${user.email}:`, err);
    }
  }
}

async function addSampleProperties() {
  console.log('Adding sample properties...');
  
  // Get the owner user ID
  const { data: userData, error: userError } = await supabase.auth.signInWithPassword({
    email: 'owner@homygo.test',
    password: 'owner123'
  });
  
  if (userError || !userData.user) {
    console.error('Could not sign in as owner to add properties:', userError?.message);
    return;
  }
  
  const ownerId = userData.user.id;
  
  for (const property of sampleProperties) {
    try {
      const { data, error } = await supabase
        .from('properties')
        .insert({
          ...property,
          user_id: ownerId
        });
        
      if (error) {
        console.error(`Error adding property ${property.title}:`, error.message);
      } else {
        console.log(`✅ Property added: ${property.title}`);
      }
    } catch (err) {
      console.error(`Exception adding property ${property.title}:`, err);
    }
  }
  
  // Sign out after adding properties
  await supabase.auth.signOut();
}

async function setupTestData() {
  console.log('🚀 Setting up test data for Homygo...\n');
  
  await createTestUsers();
  console.log('\n⏳ Waiting 2 seconds before adding properties...\n');
  await new Promise(resolve => setTimeout(resolve, 2000));
  await addSampleProperties();
  
  console.log('\n✅ Test data setup complete!');
  console.log('\n📋 Test Credentials:');
  console.log('Owner: owner@homygo.test / owner123');
  console.log('Guest: guest@homygo.test / guest123');
  console.log('\n🌐 Visit http://localhost:5173 to test the application');
}

// Run the setup
setupTestData().catch(console.error);
