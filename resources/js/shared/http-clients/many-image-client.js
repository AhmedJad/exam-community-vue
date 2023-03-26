const BASE_URL = `many-image`;
export default {
    store(formValue) {
        return axios.post(`${BASE_URL}`, formValue);
    },
    addImage(formValue) {
        return axios.post(`${BASE_URL}/add-image`, formValue);
    },
    update(formValue) {
        return axios.post(`${BASE_URL}/update`, formValue);
    },
    delete(id) {
        return axios.delete(`${BASE_URL}/${id}`);
    },
    deleteImage(id, image, imageIndex) {
        return axios.delete(`${BASE_URL}/delete-image?id=${id}&image=${image}&image_index=${imageIndex}`);
    },
    addImage(formValue) {
        return axios.post(`${BASE_URL}/add-image`, formValue);
    },
    getHellos(pageNo, pageSize) {
        return axios.get(`${BASE_URL}?page=${pageNo}&page_size=${pageSize}`);
    },
    getAllHellos() {
        return axios.get(`${BASE_URL}`);
    }
}