import React from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { PROPERTY_TYPES } from '@/data/propertiesData';
import { BARANGAYS as CDO_BARANGAYS_ALL } from '@/data/locations';

const CDO_BARANGAYS = CDO_BARANGAYS_ALL.filter(b => b !== 'All Locations' && !b.includes('(Sample Other City)'));
const propertyStatuses = ['draft', 'live', 'booked', 'unlisted'];

const PropertyFormFields = ({ data, setData }) => {
  const handleChange = (e) => {
    const { id, value } = e.target;
    setData(prev => ({ ...prev, [id]: value }));
  };

  const handleSelectChange = (field) => (value) => {
    setData(prev => ({ ...prev, [field]: value }));
  };

  return (
    <div className="space-y-6">
      <div>
        <Label htmlFor="title" className="text-sm font-medium text-muted-foreground">Property Title</Label>
        <Input id="title" value={data.title} onChange={handleChange} required className="mt-1 min-h-[44px]" placeholder="e.g., Cozy 2BR Apartment near City Center"/>
      </div>

      <div>
        <Label htmlFor="description" className="text-sm font-medium text-muted-foreground">Description</Label>
        <textarea id="description" value={data.description} onChange={handleChange} required rows="4" className="mt-1 w-full rounded-md border border-input-border bg-input px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 min-h-[100px]" placeholder="Detailed description of your property..."></textarea>
      </div>
      
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <Label htmlFor="propertyType" className="text-sm font-medium text-muted-foreground">Property Type</Label>
           <Select value={data.propertyType} onValueChange={handleSelectChange('propertyType')} required>
              <SelectTrigger className="w-full mt-1 min-h-[44px] bg-input border-input-border">
                <SelectValue placeholder="Select property type" />
              </SelectTrigger>
              <SelectContent>
                {PROPERTY_TYPES.filter(pt => pt !== 'All Types' && ['Apartment', 'House', 'Condo', 'Hotel', 'Inn'].includes(pt)).map(pt => (
                  <SelectItem key={pt} value={pt}>{pt}</SelectItem>
                ))}
              </SelectContent>
            </Select>
        </div>
        <div>
          <Label htmlFor="barangay" className="text-sm font-medium text-muted-foreground">Barangay (CDO Only)</Label>
           <Select value={data.barangay} onValueChange={handleSelectChange('barangay')} required>
              <SelectTrigger className="w-full mt-1 min-h-[44px] bg-input border-input-border">
                <SelectValue placeholder="Select Barangay" />
              </SelectTrigger>
              <SelectContent>
                {CDO_BARANGAYS.map(b => (
                  <SelectItem key={b} value={b}>{b}</SelectItem>
                ))}
              </SelectContent>
            </Select>
        </div>
      </div>
      <div>
        <Label htmlFor="addressDetails" className="text-sm font-medium text-muted-foreground">Address Details (Street, Building, etc.)</Label>
        <Input id="addressDetails" value={data.addressDetails} onChange={handleChange} className="mt-1 min-h-[44px]" placeholder="e.g., Unit 123, Tower A, Main Street"/>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <Label htmlFor="price" className="text-sm font-medium text-muted-foreground">Price per Month (PHP)</Label>
          <Input id="price" type="number" value={data.price} onChange={handleChange} required className="mt-1 min-h-[44px]" placeholder="e.g., 15000"/>
        </div>
        <div>
          <Label htmlFor="areaSqm" className="text-sm font-medium text-muted-foreground">Area (sqm)</Label>
          <Input id="areaSqm" type="number" value={data.areaSqm} onChange={handleChange} className="mt-1 min-h-[44px]" placeholder="e.g., 55"/>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <Label htmlFor="bedrooms" className="text-sm font-medium text-muted-foreground">Bedrooms</Label>
          <Input id="bedrooms" type="number" value={data.bedrooms} onChange={handleChange} className="mt-1 min-h-[44px]" placeholder="e.g., 2"/>
        </div>
        <div>
          <Label htmlFor="bathrooms" className="text-sm font-medium text-muted-foreground">Bathrooms</Label>
          <Input id="bathrooms" type="number" value={data.bathrooms} onChange={handleChange} className="mt-1 min-h-[44px]" placeholder="e.g., 1"/>
        </div>
      </div>
       <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
              <Label htmlFor="brand" className="text-sm font-medium text-muted-foreground">Brand (Optional)</Label>
              <Input id="brand" value={data.brand} onChange={handleChange} className="mt-1 min-h-[44px]" placeholder="e.g., Avida, Kingsfield"/>
          </div>
          <div>
              <Label htmlFor="status" className="text-sm font-medium text-muted-foreground">Listing Status</Label>
              <Select value={data.status} onValueChange={handleSelectChange('status')} required>
                  <SelectTrigger className="w-full mt-1 min-h-[44px] bg-input border-input-border">
                  <SelectValue placeholder="Select status" />
                  </SelectTrigger>
                  <SelectContent>
                  {propertyStatuses.map(s => (
                      <SelectItem key={s} value={s}>{s.charAt(0).toUpperCase() + s.slice(1)}</SelectItem>
                  ))}
                  </SelectContent>
              </Select>
          </div>
      </div>
    </div>
  );
};

export default PropertyFormFields;