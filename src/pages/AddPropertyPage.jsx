import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { motion } from 'framer-motion';
import { supabase } from '@/lib/supabaseClient.js';
import { useAuth } from '@/contexts/AuthContext.jsx';
import { toast } from '@/components/ui/use-toast';
import { Button } from '@/components/ui/button';
import { Loader2, ArrowLeft, PlusCircle } from 'lucide-react';
import { BARANGAYS as CDO_BARANGAYS_ALL } from '@/data/locations';

import PropertyFormHeader from '@/components/add-property/PropertyFormHeader';
import PropertyFormFields from '@/components/add-property/PropertyFormFields';
import PropertyImageUploader from '@/components/add-property/PropertyImageUploader';

const CDO_BARANGAYS = CDO_BARANGAYS_ALL.filter(b => b !== 'All Locations' && !b.includes('(Sample Other City)'));

const AddPropertyPage = () => {
  const navigate = useNavigate();
  const { propertyId } = useParams();
  const { user } = useAuth();
  
  const [isEditMode, setIsEditMode] = useState(false);
  const [loadingPropertyData, setLoadingPropertyData] = useState(false);
  
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    propertyType: '',
    barangay: '',
    addressDetails: '',
    price: '',
    bedrooms: '',
    bathrooms: '',
    areaSqm: '',
    brand: '',
    status: 'draft',
  });

  const [images, setImages] = useState([]); // Files for new uploads
  const [imagePreviews, setImagePreviews] = useState([]); // Previews for new uploads
  const [existingImagePaths, setExistingImagePaths] = useState([]); // URLs of existing images for edit mode
  
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (propertyId) {
      setIsEditMode(true);
      setLoadingPropertyData(true);
      const fetchPropertyData = async () => {
        const { data, error } = await supabase
          .from('properties')
          .select('*')
          .eq('id', propertyId)
          .eq('user_id', user.id)
          .single();
        
        if (error || !data) {
          toast({ title: "Error", description: "Could not fetch property data or property not found.", variant: "destructive"});
          navigate('/owner/dashboard');
          return;
        }
        
        let fetchedBarangay = '';
        let fetchedAddressDetails = '';
        const locationParts = data.location?.split(',');
        if (locationParts && locationParts.length > 1 && CDO_BARANGAYS.includes(locationParts[0].trim())) {
            fetchedBarangay = locationParts[0].trim();
            fetchedAddressDetails = locationParts.slice(1).join(',').trim();
        } else if (CDO_BARANGAYS.includes(data.location?.trim())) {
            fetchedBarangay = data.location.trim();
        } else {
            fetchedAddressDetails = data.location || '';
        }

        setFormData({
            title: data.title || '',
            description: data.description || '',
            propertyType: data.property_type || '',
            barangay: fetchedBarangay,
            addressDetails: fetchedAddressDetails,
            price: data.price?.toString() || '',
            bedrooms: data.bedrooms?.toString() || '',
            bathrooms: data.bathrooms?.toString() || '',
            areaSqm: data.area_sqm?.toString() || '',
            brand: data.brand || '',
            status: data.status || 'draft',
        });
        
        setExistingImagePaths(data.image_paths || []);
        setLoadingPropertyData(false);
      };
      fetchPropertyData();
    }
  }, [propertyId, user, navigate]);

  const handleImageChange = (e) => {
    const files = Array.from(e.target.files);
    const totalImages = (existingImagePaths?.length || 0) + images.length + files.length;
    if (totalImages > 5) {
      toast({ title: "Image Limit", description: "You can upload a maximum of 5 images in total.", variant: "destructive" });
      return;
    }
    setImages(prev => [...prev, ...files.slice(0, 5 - ((existingImagePaths?.length || 0) + prev.length))]);

    const newPreviews = files.map(file => URL.createObjectURL(file));
    setImagePreviews(prev => [...prev, ...newPreviews.slice(0, 5 - ((existingImagePaths?.length || 0) + prev.length))]);
  };

  const removeNewImage = (index) => {
    setImages(prev => prev.filter((_, i) => i !== index));
    const newPreviews = imagePreviews.filter((_, i) => i !== index);
    URL.revokeObjectURL(imagePreviews[index]); 
    setImagePreviews(newPreviews);
  };
  
  const removeExistingImage = async (pathToRemove) => {
    setExistingImagePaths(prev => prev.filter(path => path !== pathToRemove));
    toast({ title: "Image Marked for Removal", description: "Image will be removed from storage upon saving changes.", variant: "default"});
  };
  
  useEffect(() => {
    return () => imagePreviews.forEach(preview => URL.revokeObjectURL(preview));
  }, [imagePreviews]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (isSubmitting) return;
    
    setIsSubmitting(true);

    const totalExistingImages = existingImagePaths?.length || 0;
    const totalNewImages = images.length;

    if (totalExistingImages + totalNewImages === 0) {
        toast({title: "Image Required", description: "Please upload at least one image for your property.", variant: "destructive"});
        setIsSubmitting(false);
        return;
    }

    const uploadedImagePaths = [...existingImagePaths];
    for (const image of images) {
        const fileName = `${user.id}/${Date.now()}-${image.name.replace(/\s+/g, '_')}`;
        const { data: uploadData, error: uploadError } = await supabase.storage
            .from('property-images') 
            .upload(fileName, image);

        if (uploadError) {
            console.error("Image upload error: ", uploadError);
            toast({title: "Image Upload Failed", description: uploadError.message, variant: "destructive"});
            setIsSubmitting(false);
            return;
        }
        const { data: { publicUrl } } = supabase.storage.from('property-images').getPublicUrl(uploadData.path);
        uploadedImagePaths.push(publicUrl);
    }
    
    const { barangay, addressDetails, propertyType, title, status, ...restOfData } = formData;
    const fullLocation = barangay && addressDetails ? `${barangay}, ${addressDetails}` : (barangay || addressDetails);

    const propertyDataToSubmit = {
      ...restOfData,
      user_id: user.id,
      title,
      status,
      property_type: propertyType,
      location: fullLocation,
      price: parseFloat(formData.price),
      bedrooms: parseInt(formData.bedrooms) || null,
      bathrooms: parseInt(formData.bathrooms) || null,
      area_sqm: parseFloat(formData.areaSqm) || null,
      image_paths: uploadedImagePaths,
      guest_favorite: Math.random() > 0.8,
      rating: parseFloat((Math.random() * (5 - 3.5) + 3.5).toFixed(1)),
      availabilityStatus: status === 'live' ? 'Available' : (status === 'booked' ? 'Booked' : 'Unavailable'),
    };

    try {
      if (isEditMode) {
        const { error } = await supabase
          .from('properties')
          .update(propertyDataToSubmit)
          .eq('id', propertyId)
          .eq('user_id', user.id);
        if (error) throw error;
        toast({ title: "Property Updated! 🏡", description: `${title} has been successfully updated.` });
      } else {
        const { error } = await supabase
          .from('properties')
          .insert(propertyDataToSubmit);
        if (error) throw error;
        toast({ title: "Property Added! 🎉", description: `${title} has been successfully listed.` });
      }
      navigate('/owner/dashboard');
    } catch (error) {
      console.error("Error saving property:", error.message);
      toast({
        title: `Failed to ${isEditMode ? 'Update' : 'Add'} Property`,
        description: error.message || "Could not save your property. Please try again.",
        variant: "destructive"
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  if (loadingPropertyData && isEditMode) {
    return (
      <div className="flex flex-col justify-center items-center min-h-[calc(100vh-var(--header-height))] p-4">
        <Loader2 className="w-12 h-12 animate-spin text-primary mb-4" />
        <p className="text-muted-foreground">Loading property details...</p>
      </div>
    );
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5 }}
      className="py-8 sm:py-12 bg-gradient-to-br from-background via-background to-muted/30 min-h-screen"
    >
      <div className="container mx-auto px-4 max-w-2xl">
        <Button variant="outline" onClick={() => navigate('/owner/dashboard')} className="mb-6 text-primary border-primary hover:bg-primary/10 min-h-[44px]">
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Dashboard
        </Button>
        <div className="bg-card/80 backdrop-blur-md p-6 sm:p-8 rounded-xl shadow-2xl border border-border/30">
          <PropertyFormHeader isEditMode={isEditMode} />
          <form onSubmit={handleSubmit} className="space-y-6">
            <PropertyFormFields data={formData} setData={setFormData} />
            <PropertyImageUploader
              existingImagePaths={existingImagePaths}
              imagePreviews={imagePreviews}
              onImageChange={handleImageChange}
              onRemoveNewImage={removeNewImage}
              onRemoveExistingImage={removeExistingImage}
            />
            <Button type="submit" className="w-full bg-gradient-to-r from-primary to-accent hover:from-primary/90 hover:to-accent/90 text-primary-foreground font-semibold py-3 text-base min-h-[48px]" disabled={isSubmitting}>
              {isSubmitting ? <Loader2 className="w-5 h-5 animate-spin mr-2" /> : <PlusCircle className="w-5 h-5 mr-2" />}
              {isSubmitting ? (isEditMode ? 'Updating Property...' : 'Listing Property...') : (isEditMode ? 'Update Property' : 'List My Property')}
            </Button>
          </form>
        </div>
      </div>
    </motion.div>
  );
};

export default AddPropertyPage;