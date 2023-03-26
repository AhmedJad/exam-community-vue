const BASE_URL = `exams`;
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
    getExams(pageNo,pageSize,text,parent) {
        return axios.get(`${BASE_URL}?page=${pageNo}&page_size=${pageSize}&text=${text}
        &folder_id=${parent?parent.id:''}`);
    },
    getAllExams(){
        return axios.get(`${BASE_URL}`);
    }
}