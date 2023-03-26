const BASE_URL = `exam-solutions`;
export default {
    getUsers(pageNo, pageSize, text) {
        return axios.get(`${BASE_URL}?page=${pageNo}&page_size=${pageSize}&text=${text}`);
    },
    solve(formValue) {
        return axios.post(`${BASE_URL}/solve`,formValue);
    },
    getFolders(pageNo,pageSize,text,parent,userId) {
        return axios.get(`${BASE_URL}/folders?page=${pageNo}&page_size=${pageSize}&text=${text}
        &parent_id=${parent?parent.id:''}&user_id=${userId?userId:''}`);
    },
    getExams(pageNo,pageSize,text,parent,userId) {
        return axios.get(`${BASE_URL}/exams?page=${pageNo}&page_size=${pageSize}&text=${text}
        &folder_id=${parent?parent.id:''}&user_id=${userId?userId:''}`);
    },
}