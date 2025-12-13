import apiClient from '../../../admin/js/services/apiClient.js'; // Corrected path

export default {
    template: `
        <div>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-3xl font-semibold text-gray-700">Create New Listing</h3>
                <router-link :to="{ name: 'MyListings' }" class="text-indigo-600 hover:text-indigo-900">
                    &larr; Back to Listings
                </router-link>
            </div>

            <div class="mt-8">
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
                                    <option value="" disabled>Select a type</option>
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
                                    <option value="" disabled>Select a city</option>
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
                                        <p class="text-gray-500">Is this property furnished?</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="is_published" v-model="listing.is_published" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_published" class="font-medium text-gray-700">Publish Listing</label>
                                        <p class="text-gray-500">Make this listing visible to users immediately.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Upload -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Images</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"><span>Upload files</span><input id="file-upload" name="file-upload" type="file" class="sr-only" multiple @change="handleFileChange"></label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="error" class="mt-4 text-red-500 text-sm text-center">{{ error }}</div>

                        <div class="mt-6 text-right">
                            <button type="submit" :disabled="isSubmitting" class="btn-primary inline-flex justify-center py-2 px-8 border border-transparent shadow-sm text-sm font-medium rounded-md" :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }">
                                {{ isSubmitting ? 'Saving...' : 'Save Listing' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `,
    data() {
        return {
            listing: {
                title: '',
                description: '',
                rent_amount: null,
                city: '',
                neighborhood: '',
                htype: '',
                furnished: false,
                is_published: true,
            },
            cities: [],
            imageFiles: [],
            imagePreviews: [],
            isSubmitting: false,
            error: ''
        };
    },
    async mounted() {
        await this.fetchCities();
    },
    methods: {
        async fetchCities() {
            try {
                this.cities = await apiClient.request('/cities');
            } catch (err) {
                this.error = 'Could not load cities for the form.';
            }
        },
        handleFileChange(event) {
            const files = Array.from(event.target.files);
            this.imageFiles.push(...files);

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreviews.push(e.target.result);
                };
                reader.readAsDataURL(file);
            });
        },
        removeImage(index) {
            this.imageFiles.splice(index, 1);
            this.imagePreviews.splice(index, 1);
        },
        async handleSubmit() {
            this.isSubmitting = true;
            this.error = '';

            try {
                // Step 1: Create the listing with text data
                const newListing = await apiClient.request('/listings', 'POST', this.listing);

                // Step 2: If images are selected, upload them
                if (this.imageFiles.length > 0) {
                    const uploadPromises = this.imageFiles.map(file => {
                        const formData = new FormData();
                        formData.append('image', file);
                        return apiClient.request(`/listings/${newListing.id}/images`, 'POST', formData, true); // true for multipart
                    });
                    await Promise.all(uploadPromises);
                }

                // Step 3: Redirect on success
                this.$router.push({ name: 'MyListings' });
            } catch (err) {
                this.error = err.message || 'An unexpected error occurred. Please try again.';
            } finally {
                this.isSubmitting = false;
            }
        }
    }
};