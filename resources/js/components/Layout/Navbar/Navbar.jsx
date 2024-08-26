import React, { useEffect, useState } from "react";
import { IoMdSearch } from "react-icons/io";
import { LiaShoppingBagSolid } from "react-icons/lia";
import DarkMode from "../DarkMode";
import { IoChevronDown } from "react-icons/io5";
import AppMenu from "../AppMenu/AppMenu";
import { CustomLink } from "../AppMenu/CustomComponent";
import els from "../../../assets/ico/electronik-store.svg";
import { useRef } from "react";
import { AiOutlineClose, AiOutlineMenu } from "react-icons/ai";
const Menu = [
    {
      id: "1",
      link: '/',
      name: "Home",
      withSubMenu : false 
    }, 
    {
      id: "2",
      link: "/store",
      name: "Store",
      withSubMenu : true 

    }, 
    {
        id: "3",
        link: "/feature",
        name: "Feature",
        withSubMenu : false 

    }, 
    {
        id: "4",
        link: "/blog",
        name: "Blog",
        withSubMenu: false 
    },
    {
        id: "5",
        link: "/about",
        name: "About",
        withSubMenu: false
    },
    {
        id: "6",
        link: "/source",
        name: "Source",
        withSubMenu: false

    },
    {
        id: "7",
        link: "/contact",
        name: "Contact",
        withSubMenu: false
    },
    {
        id: "8",
        link: "/partner",
        name: "Partner",
        withSubMenu: false
    }
];

const subMenu = [
    {
      parentID : "2",
      link: "/news",
      name: "Furniture Store"
    },
    {
      parentID : "2",
      link: "/yt",
      name: "Fashion Store"
    },
    {
      parentID : "2",
      link: "/news",
      name: "Electronic Store",
      ico: els
    },
    {
      parentID : "2",
      link: "/news",
      name: "Shoes Store",
      ico: els
    },
];


function Navbar() {
  const [isOpen, setIsOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const menuRef = useRef(null);

  const toggleMenu = () => {
      setIsOpen(!isOpen);
  }

  const closeMenuOutSide = (event) => {
      if(menuRef.current && menuRef.current.contains(event.target)){
          setIsOpen(false);
      }
  }

  const handleScroll = () =>{
     setIsScrolled(Window.Y > 0);
  }

  useEffect(() => {
      document.addEventListener("mousedown", closeMenuOutSide);
      window.addEventListener("scroll", handleScroll);

      return () => {
          document.removeEventListener("mousedown", closeMenuOutSide);
          window.removeEventListener("scroll", handleScroll);
      }
  })
    return (
        <div className="z-40">
            <div className='flex flex-wrap justify-center px-10 py-3 relative bg-primary'>
          
              <div id="collapseMenu"
                className="max-lg:hidden  lg:!block max-lg:before:fixed max-lg:before:bg-black max-lg:before:opacity-40 max-lg:before:inset-0 max-lg:before:z-50">
                
          
                <ul
                  className=" lg:flex lg:gap-x-10 max-lg:space-y-3 max-lg:fixed max-lg:bg-primary max-lg:w-2/3 max-lg:min-w-[300px] max-lg:top-0 max-lg:left-0 max-lg:p-4 max-lg:h-full max-lg:shadow-md max-lg:overflow-auto z-50">
          
                  <AppMenu items={Menu} subMenu={subMenu}></AppMenu>
                  
                </ul>
              </div>
          
              <div className='flex ml-auto lg:hidden'>
                <button onClick={toggleMenu}>
                  { 
                    isOpen ?
                      <AiOutlineClose size={20} className="text-white" />
                      : <AiOutlineMenu size={20} className="text-white" />
                  }
                </button>
              </div>
    </div>

    <div >
    </div>
    </div>
    );
}

export default Navbar;