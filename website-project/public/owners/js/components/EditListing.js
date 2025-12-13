import apiClient from '../../../admin/js/services/apiClient.js'; // Corrected path

export default {
    template: `
        <div>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-3xl font-semibold text-gray-700">Edit Listing</h3>
                <router-link :to="{ name: 'MyListings' }" class="text-indigo-600 hover:text-indigo-900">
                    &larr; Back to Listings
                </router-link>
            </div>

            <div v-if="isLoading" class="text-center p-8">Loading listing details...</div>
            <div v-else class="mt-8">
                <div class="bg-white p-8 rounded-md shadow-md">
                    <form @submit.prevent="handleSubmit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Title -->
                            <div class="md:col-span-2">
                                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text" v-model="listing.title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea v-model="listing.description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required></textarea>
                            </div>

                            <!-- Rent Amount -->
                            <div>
                                <label for="rent_amount" class="block text-sm font-medium text-gray-700">Rent Amount (KES)</label>
                                <input type="number" v-model.number="listing.rent_amount" id="rent_amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>

                            <!-- House Type -->
                            <div>
                                <label for="htype" class="block text-sm font-medium text-gray-700">House Type</label>
                                <select v-model="listing.htype" id="htype" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <option value="SINGLE">Single</option>
                                    <option value="BEDSITTER">Bedsitter</option>
                                    <option value="STUDIO">Studio</option>
                                    <option value="ONE_BEDROOM">1 Bedroom</option>
                                    <option value="TWO_BEDROOM">2 Bedroom</option>
                                </select>
                            </div>

                            <!-- City -->
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                <select v-model="listing.city" id="city" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                                </select>
                            </div>

                            <!-- Neighborhood -->
                            <div>
                                <label for="neighborhood" class="block text-sm font-medium text-gray-700">Neighborhood</label>
                                <input type="text" v-model="listing.neighborhood" id="neighborhood" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>

                            <!-- Features -->
                            <div class="md:col-span-2 space-y-2">
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="furnished" v-model="listing.furnished" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="furnished" class="font-medium text-gray-700">Furnished</label>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="is_published" v-model="listing.is_published" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_published" class="font-medium text-gray-700">Publish Listing</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Image Management Section -->
                    <div class="mt-8">
                        <h4 class="text-lg font-medium text-gray-800">Manage Images</h4>
                        <div v-if="listing.images && listing.images.length" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="(image, index) in listing.images" :key="index" class="relative group">
                                <img :src="image" alt="Listing image" class="rounded-md object-cover h-32 w-full">
                                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="deleteImage(listing.id, index)" class="text-white text-xs bg-red-600 hover:bg-red-700 rounded-full p-2">Delete</button>
                                </div>
                            </div>
                        </div>
                        <p v-else class="mt-2 text-sm text-gray-500">This listing has no images.</p>

                        <!-- Image Upload -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">Upload New Images</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"><span>Upload files</span><input id="file-upload" name="file-upload" type="file" class="sr-only" multiple @change="handleFileChange"></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Image Previews -->
                        <div v-if="newImagePreviews.length > 0" class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">New Images to Upload</label>
                            <div class="mt-2 grid grid-cols-3 md:grid-cols-6 gap-4">
                                <div v-for="(preview, index) in newImagePreviews" :key="index" class="relative group">
                                    <img :src="preview" class="rounded-md object-cover h-24 w-full">
                                    <button @click.prevent="removeNewImage(index)" class="absolute top-0 right-0 -mt-2 -mr-2 text-white bg-red-600 rounded-full h-6 w-6 flex items-center justify-center text-xs">X</button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div v-if="error" class="mt-4 text-red-500 text-sm text-center">{{ error }}</div>

                    <div class="mt-6 text-right">
                        <button @click="handleSubmit" :disabled="isSubmitting" class="btn-primary inline-flex justify-center py-2 px-8 border border-transparent shadow-sm text-sm font-medium rounded-md" :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }">
                            {{ isSubmitting ? 'Saving...' : 'Update Listing' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `,
    data() {
        return {
            listing: {},
            cities: [],
            newImageFiles: [],
            newImagePreviews: [],
            isLoading: true,
            isSubmitting: false,
            error: ''
        };
    },
    async mounted() {
        this.isLoading = true;
        const listingId = this.$route.params.id;
        try {
            const [listingData, citiesData] = await Promise.all([
                apiClient.request(`/listings/${listingId}`),
                apiClient.request('/cities')
            ]);
            this.listing = listingData;
            // The neighborhood is an object, so we need to extract the name for the input field
            this.listing.neighborhood = listingData.neighborhood.name;
            this.cities = citiesData;
        } catch (err) {
            this.error = 'Failed to load listing data. Please try again.';
        } finally {
            this.isLoading = false;
        }
    },
    methods: {
        handleFileChange(event) {
            this.newImageFiles.push(...Array.from(event.target.files));
            
            // Generate previews for the new files
            Array.from(event.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.newImagePreviews.push(e.target.result);
                };
                reader.readAsDataURL(file);
            });
        },
        async handleSubmit() {
            this.isSubmitting = true;
            this.error = '';
            const listingId = this.$route.params.id;

            try {
                // Step 1: Update text data
                await apiClient.request(`/listings/${listingId}`, 'PUT', this.listing);

                // Step 2: Upload any new images
                if (this.newImageFiles.length > 0) {
                    const uploadPromises = this.newImageFiles.map(file => {
                        const formData = new FormData();
                        formData.append('image', file);
                        return apiClient.request(`/listings/${listingId}/images`, 'POST', formData, true);
                    });
                    await Promise.all(uploadPromises);
                }

                this.$router.push({ name: 'MyListings' });
            } catch (err) {
                this.error = err.message || 'An unexpected error occurred.';
            } finally {
                this.isSubmitting = false;
            }
        },
        removeNewImage(index) {
            this.newImageFiles.splice(index, 1);
            this.newImagePreviews.splice(index, 1);
        },
        // Note: deleteImage method would require backend support for deleting a specific image by its URL or ID.
        async deleteImage(listingId, imageIndex) {
            const imageUrl = this.listing.images[imageIndex];
            if (!confirm('Are you sure you want to delete this image?')) return;
            try {
                await apiClient.request(`/listings/${listingId}/images`, 'DELETE', { imageUrl });
                this.listing.images.splice(imageIndex, 1); // Remove from UI immediately
            } catch (err) {
                this.error = 'Failed to delete image.';
            }
        }
    }
};