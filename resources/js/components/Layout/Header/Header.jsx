import React from "react";
import { IoMdSearch } from "react-icons/io";
import { useState, useEffect } from "react";
import SearchBar from "../_partials/custom/searchBar";
import { useSelector } from "react-redux";
import  Cart  from "../Cart/Cart";
import { ModalProvider, useModal } from "../_partials/custom/ModalContext.jsx";
import { OpenModalButton } from "../_partials/custom/Modal.jsx";
const Header = () =>{
    const [isScrolled, setIsScrolled] = useState(false);

    const products = useSelector((state) => state.appReducer.products);

    const handleScroll = () =>{
        if(window.scrollY > 80){
            setIsScrolled(true);
        }else{
            setIsScrolled(false);
        }  

    }

    useEffect(() => {
        window.addEventListener("scroll", handleScroll);

        return () => {
            window.removeEventListener("scroll", handleScroll);
        }
    });
    
    const openModal = () => {};
    return (
        <header className={` ${isScrolled ? 'fixed top-0 w-full shadow-md' : 'relative' }
         flex bg-white border-b py-4 sm:px-8 px-6 font-[sans-serif] min-h-[80px]
          tracking-wide  z-50`}>
            <div className='flex flex-wrap items-center lg:gap-y-2 gap-4 w-full'>
            <div className="mx-auto max-w-screen-md lg:hidden"> 
                    <SearchBar />
            </div>
            <hr className="bg-gray-900 w-full lg:hidden" />
            <a href="javascript:void(0)" className="text-2xl text-secondary">LesTruviens
            </a>
        
        
            <div className="flex gap-x-6 gap-y-4 ml-auto">
                 <div className="mx-auto max-w-screen-md hidden lg:block"> 
                    <SearchBar />
                </div>

        
                <div className='flex items-center space-x-8'>
                <span className="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20px" className="cursor-pointer fill-[#333] inline"
                    viewBox="0 0 64 64">
                    <path
                        d="M45.5 4A18.53 18.53 0 0 0 32 9.86 18.5 18.5 0 0 0 0 22.5C0 40.92 29.71 59 31 59.71a2 2 0 0 0 2.06 0C34.29 59 64 40.92 64 22.5A18.52 18.52 0 0 0 45.5 4ZM32 55.64C26.83 52.34 4 36.92 4 22.5a14.5 14.5 0 0 1 26.36-8.33 2 2 0 0 0 3.27 0A14.5 14.5 0 0 1 60 22.5c0 14.41-22.83 29.83-28 33.14Z"
                        data-original="#000000" />
                    </svg>
                    <span className="absolute left-auto -ml-1 top-0 rounded-full bg-red-500 px-1 py-0 text-xs text-white">0</span>
                </span>

                <ModalProvider>
                    <div className="relative">
                        <OpenModalButton>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" className="cursor-pointer fill-[#333] inline"
                            viewBox="0 0 512 512">
                            <path
                                d="M164.96 300.004h.024c.02 0 .04-.004.059-.004H437a15.003 15.003 0 0 0 14.422-10.879l60-210a15.003 15.003 0 0 0-2.445-13.152A15.006 15.006 0 0 0 497 60H130.367l-10.722-48.254A15.003 15.003 0 0 0 105 0H15C6.715 0 0 6.715 0 15s6.715 15 15 15h77.969c1.898 8.55 51.312 230.918 54.156 243.71C131.184 280.64 120 296.536 120 315c0 24.812 20.188 45 45 45h272c8.285 0 15-6.715 15-15s-6.715-15-15-15H165c-8.27 0-15-6.73-15-15 0-8.258 6.707-14.977 14.96-14.996zM477.114 90l-51.43 180H177.032l-40-180zM150 405c0 24.813 20.188 45 45 45s45-20.188 45-45-20.188-45-45-45-45 20.188-45 45zm45-15c8.27 0 15 6.73 15 15s-6.73 15-15 15-15-6.73-15-15 6.73-15 15-15zm167 15c0 24.813 20.188 45 45 45s45-20.188 45-45-20.188-45-45-45-45 20.188-45 45zm45-15c8.27 0 15 6.73 15 15s-6.73 15-15 15-15-6.73-15-15 6.73-15 15-15zm0 0"
                                data-original="#000000"></path>
                            </svg>
                            <span  className="absolute left-auto -ml-1 top-0 rounded-full bg-red-500 px-1 py-0 text-xs text-white">
                                { products.length > 0 ? products.length : 0 }
                            </span>
                        </OpenModalButton>
                        <ul className="z-80 absolute">
                                <Cart products={products}/>
                        </ul>
                    </div>
                </ModalProvider>
                <button
                    className='px-5 py-2 text-sm rounded-full text-white border-2 border-[#007bff] bg-[#007bff] hover:bg-[#004bff]'>Sign
                    In</button>
        
                {/* <button id="toggleOpen" className='lg:hidden'>
                    <svg className="w-7 h-7" fill="#333" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                        clip-rule="evenodd"></path>
                    </svg>
                </button> */}
                </div>
            </div>
            </div>
        </header>
    );
}

export default Header;