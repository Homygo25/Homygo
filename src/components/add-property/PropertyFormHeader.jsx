import React from 'react';
import { Home } from 'lucide-react';

const PropertyFormHeader = ({ isEditMode }) => (
  <div className="text-center mb-6 sm:mb-8">
    <Home className="w-12 h-12 text-primary mx-auto mb-3" />
    <h1 className="text-2xl sm:text-3xl font-bold text-foreground">{isEditMode ? "Edit Property" : "List Your Property"}</h1>
    <p className="text-muted-foreground text-sm sm:text-base">Fill in the details below to showcase your rental.</p>
  </div>
);

export default PropertyFormHeader;