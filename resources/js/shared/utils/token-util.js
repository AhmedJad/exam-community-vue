export default {
    KEY : 'TOKEN',
    set(token) {
        localStorage.setItem(this.KEY, token);
    },
    get() {
        return localStorage.getItem(this.KEY);
    },
    remove() {
        localStorage.removeItem(this.KEY);
    },
    payload() {
        return this.get() ? this.decode(this.get().split(".")[1]) : null;
    },
    getUser() {
        return {
            id: this.payload().id,
            name: this.payload().name,
            phone: this.payload().phone,
            address: this.payload().address,
            city: this.payload().city,
            age: this.payload().age,
            education: this.payload().education,
            email: this.payload().email,
            job: this.payload().job,
            about_me: this.payload().joabout_me,
            email_verified_at: this.payload().email_verified_at,
            image: this.payload().image,
        };
    },
    //Commons
    decode(payload) {
        return JSON.parse(this.b64DecodeUnicode(payload));
    },
    b64DecodeUnicode(str) {
        return decodeURIComponent(Array.prototype.map.call(atob(str), function (c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
    }
}