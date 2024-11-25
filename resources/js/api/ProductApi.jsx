import ApiServices from "../Services/ApiServices";


const ProductApi = {
    getAllProduct: async() =>{
        try{
            const response = await ApiServices.get("products");
            return response.data;

        }catch(error){
            throw error;
        }
    }
}

export default ProductApi;