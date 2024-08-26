import React, { useState } from "react";
import { FaShareSquare} from "react-icons/fa";
import { CustomSocialButton } from "../_partials/custom/component";
import { IoIosHeartEmpty } from "react-icons/io";
import { FaRegCommentDots } from "react-icons/fa6";
import { BiLike } from "react-icons/bi";
import { useNavigate } from "react-router-dom";
import { addToCart } from "../../../redux/appSlicer";
import { toast } from "react-toastify";
import { useDispatch } from "react-redux";

const ProductCard = ({ product }) =>{
    const dispatch = useDispatch();
    const name = product.name;
    const id = product.id;
    const idString = (id) =>{
        return String(id).toLowerCase().split(" ").join("");
    }

    const rootId = idString(id);
    const [wishList, setWishList] = useState([]);
    const navigate = useNavigate();
    const handleProductDetails = () =>{
        navigate(`/product/${rootId}`, {
            state : {
                item : product
            },
        })
    }

    const handleWishList = () =>{
        toast.success("Product added to wish list");
        setWishList(wishList.push(product));
        console.log(wishList);
    }

    return (
        <div
            data-aos="zoom-in" 
            data-aos-delay = {product.aosDelay} 
            key={product.id}
           
            className="relative group flex w-full max-w-xs flex-col overflow-hidden rounded-lg border border-gray-100 bg-white shadow-md">
            
            {/* Following component */}
            <div className="flex items-center m-2">
                <img className="h-5 w-5 rounded-full object-cover" src="https://images.unsplash.com/photo-1709477542149-f4e0e21d590b?q=80&w=1887&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Simon Lewis" />
                <div className="ml-4 w-56">
                    <p className="text-slate-800 text-[12px] font-extrabold">Richard Hendricks</p>
                    <p className="text-slate-500 text-[9px]">Algorithms Expert</p>
                </div>
                <button className="flex items-center justify-center gap-1 rounded-lg border border-primary px-2 py-1 font-medium text-primary focus:outline-none focus:ring hover:bg-primary/40">
                    <span>Follow</span>
                </button>
            </div>
            
            {/* Social Button of product */}
            <div className="absolute -right-16 top-32 z-40 mr-2 mb-4 space-y-2 transition-all duration-300 group-hover:right-0">
            <CustomSocialButton>
                <div className="block" >
                    <BiLike className="h-7 w-7 text-white"/>
                    <div className="text-[9px]">7.3k</div>
                </div>
            </CustomSocialButton>

            <CustomSocialButton>
                <div className="block">
                    <FaRegCommentDots className="mr-1 h-5 w-5"/>
                    <div className="text-[9px]">12.0k</div>
                </div>
            </CustomSocialButton>

            <CustomSocialButton>
                <div className="block">
                    <FaShareSquare className="mr-1 h-5 w-5 text-white"/>
                    <div className="text-[9px]">12.0k</div>
                </div>
            </CustomSocialButton>

            <CustomSocialButton>
                <b className="align-middle">...</b>
            </CustomSocialButton>
            </div>
            <a className="relative  mt-3 flex  overflow-hidden w-full" href="#">
                <img className="object-cover lg:h-[300px] sm:h-[250px] transition-all duration-300 group-hover:scale-125 w-full" src={product.coverImage}  alt={product.name} />
                <span className="absolute top-0 left-0 m-2 rounded-full bg-black px-2 text-center text-sm font-medium text-white">39% OFF</span>
                <span onClick={handleWishList} className="absolute top-0 right-0 m-2 rounded-full px-2 text-center text-sm font-medium text-white">
                    <IoIosHeartEmpty className="text-2xl"/>
                </span>
            </a>
            <div className="mt-4 px-5 pb-5">
                <a href="#">
                <h5 className="text-xl tracking-tight text-slate-900">{product.name}</h5>
                </a>
                <div className="mt-2 mb-5 flex items-center justify-between">
                <p>
                    <span className="text-3xl font-bold text-slate-900">${Math.round(product.price*100)/100}</span>
                    <span className="text-sm text-slate-900 line-through">$699</span>
                </p>
                <div className="flex items-center">
                    <svg aria-hidden="true" className="h-5 w-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <svg aria-hidden="true" className="h-5 w-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <svg aria-hidden="true" className="h-5 w-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <svg aria-hidden="true" className="h-5 w-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <svg aria-hidden="true" className="h-5 w-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <span className="mr-2 ml-3 rounded bg-yellow-200 px-2.5 py-0.5 text-xs font-semibold">5.0</span>
                </div>
                </div>
                <a 
                    onClick={() => 
                        dispatch(
                            addToCart({
                               id: product.id,
                               name: product.name,
                               quantity: 1,
                               price: product.price,
                               image: product.coverImage
                            })
                        )
                    } 
                className="cursor-pointer flex items-center justify-center rounded-md bg-primary px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-300">
                    <svg xmlns="http://www.w3.org/2000/svg" className="mr-2 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                Add to cart</a
                >
              
            </div>
            </div>

    )
}

export default ProductCard;