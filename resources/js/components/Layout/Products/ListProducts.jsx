import React, { useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useEffect } from "react";
import { fetchTodo } from "../../../redux/todoSlicer";
import ProductApi from "../../../api/ProductApi"
import Product from "../../../Modeles/Product";
import ProductCard from "./ProductCard";
import ShopSideNav from "../ShopSideNav";

const ListProducts = () => {
    const [productList, setProductList] = useState([]);

    useEffect(() => {
        const fetchProducts = async() =>{
            try {
                
                const data = await ProductApi.getAllProduct();
                const productL = data.data.map(item => Product.fromApiResponse(item));
                setProductList(productL);
            } catch (error) {
                console.error('Error fetching products:', error);
            }
        }
        fetchProducts();
    }, []);



    return (

    <div className="max-w-container mx-auto px-4 py-2">
        {/* <Breadcrumbs title="Products" /> */}
        {/* ================= Products Start here =================== */}
        <div className="w-full h-full flex pb-15 gap-10">
          <div className="w-[20%] lgl:w-[25%] hidden mdl:inline-flex h-full py-20 px-4 ">
            <ShopSideNav />
           
          </div>
                    <div className="w-full mdl:w-[80%] lgl:w-[75%] h-full flex flex-col gap-10">
            {/* <ProductBanner itemsPerPageFromBanner={itemsPerPageFromBanner} /> */}
            {/* <Pagination itemsPerPage={itemsPerPage} /> */}
            
            <h2 className="text-2xl underline uppercase text-center font-extrabold text-gray-800">Products List</h2>
            <div className="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {
                    productList.map((data) => (
                        <ProductCard product={data} />
                    ))
                    
                }
            </div>
          </div>
        </div>
        {/* ================= Products End here ===================== */}
      </div>

    )
}

export default ListProducts;