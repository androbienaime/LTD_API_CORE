import React from "react";
import { IoCamera } from "react-icons/io5";
import { useRef } from "react";


const SearchBar = () =>{
    const fileInputRef = useRef(null)
    const handleFileClick = ()=>{
        fileInputRef.current.click();
    };

    return (
        <form className="relative mx-auto flex w-full max-w-2xl items-center justify-between rounded-md border border-primary"> 
            <IoCamera onClick={handleFileClick} className="absolute left-2 block h-5 w-5 text-gray-400 cursor-pointer hover:text-primary" />
            <input type="file" className="hidden" ref={fileInputRef} accept="image/*" name="search-picture" />
            <input type="name" name="search" className="h-10 w-full rounded-md py-4 pr-40 pl-12 outline-none focus:ring-2" placeholder="Produit, Boutique, Per..." />
            <button type="submit" className="absolute right-0 mr-1 inline-flex h-8 items-center justify-center rounded-lg bg-primary px-8 font-medium text-white focus:ring-4 hover:bg-gray-700">Search</button>
        </form>
    )
}

export default SearchBar;