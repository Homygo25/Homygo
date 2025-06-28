import React from 'react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { UploadCloud, X } from 'lucide-react';

const PropertyImageUploader = ({ existingImagePaths, imagePreviews, onImageChange, onRemoveNewImage, onRemoveExistingImage }) => {
  return (
    <div>
      <Label htmlFor="images" className="text-sm font-medium text-muted-foreground">Property Images (Max 5 total)</Label>
      <div className="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-input-border border-dashed rounded-md min-h-[150px] hover:border-primary transition-colors bg-input/50">
        <div className="space-y-1 text-center">
          <UploadCloud className="mx-auto h-12 w-12 text-muted-foreground" />
          <div className="flex text-sm text-muted-foreground">
            <label
              htmlFor="images-upload"
              className="relative cursor-pointer rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary"
            >
              <span>Upload files</span>
              <input id="images-upload" name="images-upload" type="file" className="sr-only" multiple accept="image/*" onChange={onImageChange} />
            </label>
            <p className="pl-1">or drag and drop</p>
          </div>
          <p className="text-xs text-muted-foreground/80">PNG, JPG, GIF up to 10MB each</p>
        </div>
      </div>
      {(existingImagePaths.length > 0 || imagePreviews.length > 0) && (
        <div className="mt-4">
          <p className="text-xs text-muted-foreground mb-2">Current images ({existingImagePaths.length + imagePreviews.length} / 5):</p>
          <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
            {existingImagePaths.map((path, index) => (
              <div key={`existing-${index}`} className="relative group aspect-square">
                <img src={path} alt={`Existing image ${index + 1}`} className="w-full h-full object-cover rounded-md border border-border" />
                <Button
                  type="button"
                  variant="destructive"
                  size="icon"
                  className="absolute top-1 right-1 w-6 h-6 opacity-0 group-hover:opacity-100 transition-opacity z-10"
                  onClick={() => onRemoveExistingImage(path)}
                >
                  <X className="w-4 h-4" />
                </Button>
              </div>
            ))}
            {imagePreviews.map((preview, index) => (
              <div key={`new-${index}`} className="relative group aspect-square">
                <img src={preview} alt={`Preview ${index + 1}`} className="w-full h-full object-cover rounded-md border border-border" />
                <Button
                  type="button"
                  variant="destructive"
                  size="icon"
                  className="absolute top-1 right-1 w-6 h-6 opacity-0 group-hover:opacity-100 transition-opacity z-10"
                  onClick={() => onRemoveNewImage(index)}
                >
                  <X className="w-4 h-4" />
                </Button>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default PropertyImageUploader;