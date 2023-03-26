const BASE_URL = `folders`;
export default {
    store(formValue) {
        return axios.post(`${BASE_URL}`, formValue);
    },
    update(formValue) {
        return axios.post(`${BASE_URL}/update`, formValue);
    },
    delete(id) {
        return axios.delete(`${BASE_URL}/${id}`);
    },
    getFolders(pageNo,pageSize,text,parent) {
        return axios.get(`${BASE_URL}?page=${pageNo}&page_size=${pageSize}&text=${text}
        &parent_id=${parent?parent.id:''}`);
    },
    getAllFolders(){
        return axios.get(`${BASE_URL}`);
    }
}