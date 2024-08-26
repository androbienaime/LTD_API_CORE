import React from "react";
import Image1 from "../../../assets/products/1.jpeg";
import Image2 from "../../../assets/products/2.jpeg";
import Image3 from "../../../assets/products/3.jpeg";
import Image4 from "../../../assets/products/4.jpeg";
import Image5 from "../../../assets/products/5.jpeg";
import Image6 from "../../../assets/products/6.jpeg";
import Image7 from "../../../assets/products/7.jpeg";
import { FaStar } from "react-icons/fa";
const ProductsData = [
    {
        id: 1,
        img : Image1,
        title : "Sneakers",
        rating: "5.0",
        author : "John",
        aosDelay: "0"
    },
    {
        id: 2,
        img : Image2,
        title : "Sneakers",
        rating: "5.0",
        author : "John",
        aosDelay: "200"
    },
    {
        id: 3,
        img : Image3,
        title : "Sneakers",
        rating: "5.0",
        author : "John",
        aosDelay: "400"
    },
    {
        id: 4,
        img : Image4,
        title : "Sneakers",
        rating: "5.0",
        author : "John",
        aosDelay: "600"
    },
    {
        id: 5,
        img : Image5,
        title : "Sneakers",
        rating: "5.0",
        author : "John",
        aosDelay: "800"
    },
  
];
const Products = () =>{
    return (
        <div className="mt-14 mb-12">
            <div className="container">
                {/* Header Section  */}
                <div className="text-center mb-10 max-w-[600px] mx-auto">
                    <p data-aos="fade-up" className="text-sm text-primary">Top selling Products for you</p>
                    <h1 data-aos="fade-up" className="text-3xl font-bold">Products</h1>
                    <p data-aos="fade-up" className="text-xs text-gray-400">Lorem ipsum, dolor sit amet consectetur adipisicing elit. 
                        Distinctio ex assumenda</p>
                </div>
                {/* Body Section  */}
                <div>
                    <div className="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 
                    lg:grid-cols-5 place-items-center gap-5">
                        {/* Products Card  */}
                        {
                            ProductsData.map((data) => (
                                <div
                                data-aos="fade-up"
                                data-aos-delay = {data.aosDelay} 
                                key={data.id} className="space-y-3">
                                    <img alt="" src={data.img} 
                                    className="h-[220px] w-[150px] object-cover rounded-md"/>
                                    <div>
                                        <h3 className="font-semibold">{data.title}</h3>
                                        <p className="text-sm text-gray-600">{data.author}</p>
                                        <div className="flex items-center gap-1">
                                            <FaStar className="text-yellow-400" />
                                            <span>{data.rating}</span>
                                        </div>
                                    </div>
                                </div>
                            ))
                        }
                    </div>
                </div>
            </div>
        </div>
    )
}

export default Products;