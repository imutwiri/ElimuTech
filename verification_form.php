<form method="POST" enctype="multipart/form-data" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="institution" class="block text-sm font-medium text-gray-700 mb-1">Institution</label>
            <select id="institution" name="institution" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-600">
                <option value="">Select your university/college</option>
                <option value="University of Nairobi">University of Nairobi</option>
                <option value="Kenyatta University">Kenyatta University</option>
                <option value="Jomo Kenyatta University">Jomo Kenyatta University</option>
                <option value="Strathmore University">Strathmore University</option>
                <option value="Mount Kenya University">Mount Kenya University</option>
                <option value="KCA University">KCA University</option>
                <option value="Other">Other Institution</option>
            </select>
        </div>
        
        <div>
            <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">Student ID Number</label>
            <input type="text" id="student_id" name="student_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" placeholder="e.g. SC200/1234/2022">
        </div>
        
        <div class="md:col-span-2">
            <label for="program" class="block text-sm font-medium text-gray-700 mb-1">Program of Study</label>
            <input type="text" id="program" name="program" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-600" placeholder="e.g. BSc. Computer Science">
        </div>
    </div>
    
    <div class="mt-4">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Upload Student ID</h3>
        <p class="text-sm text-gray-600 mb-4">Upload clear photos of both sides of your valid student ID</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Front of ID</label>
                <div class="preview-container" id="frontPreview-container">
                    <div class="hidden" id="frontPreview-wrapper">
                        <img id="frontPreview" class="preview-image">
                        <p class="text-sm text-gray-500">Front of ID</p>
                    </div>
                    <div id="frontUpload-placeholder">
                        <i class="fas fa-camera text-gray-400 text-3xl mb-2"></i>
                        <p class="text-gray-500 mb-2">Upload front of student ID</p>
                        <label for="id_front" class="cursor-pointer bg-green-700 hover:bg-green-800 text-white text-sm font-medium py-2 px-4 rounded-md inline-block">
                            Choose File
                        </label>
                        <input type="file" id="id_front" name="id_front" class="hidden" accept="image/*">
                    </div>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Back of ID</label>
                <div class="preview-container" id="backPreview-container">
                    <div class="hidden" id="backPreview-wrapper">
                        <img id="backPreview" class="preview-image">
                        <p class="text-sm text-gray-500">Back of ID</p>
                    </div>
                    <div id="backUpload-placeholder">
                        <i class="fas fa-camera text-gray-400 text-3xl mb-2"></i>
                        <p class="text-gray-500 mb-2">Upload back of student ID</p>
                        <label for="id_back" class="cursor-pointer bg-green-700 hover:bg-green-800 text-white text-sm font-medium py-2 px-4 rounded-md inline-block">
                            Choose File
                        </label>
                        <input type="file" id="id_back" name="id_back" class="hidden" accept="image/*">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-8">
        <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 px-4 rounded-md transition duration-300">
            Submit Verification Request
        </button>
    </div>
    
    <div class="mt-4 text-center text-sm text-gray-600">
        <p>Your documents will be verified within 1-2 business days. We may contact your institution to confirm your student status.</p>
    </div>
</form>