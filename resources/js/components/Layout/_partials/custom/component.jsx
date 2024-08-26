import propTypes from "prop-types";
import { NavLink } from 'react-router-dom';

const CustomLink = ({href, className, children}) => {
    const linkStyles = "hover:text-gray-300 hover:fill-[#007bff] font-semibold text-[15px] block";
    return (
        <NavLink to={href} className={({ isActive }) => 
            isActive ? 
            `${className} ${linkStyles} text-secondary` : 
            `${className} ${linkStyles} text-white` }
        >{children}</NavLink>
    )
}

const CustomSocialButton = ({ className, children }) =>{
    const linkStyles = "flex h-10 w-10 items-center justify-center rounded-md bg-gray-900/40 text-white transition hover:bg-gray-700";

    return (
        <button className={` ${linkStyles} ${className} `}>
                {children}
        </button>
    )
}

export {CustomLink, CustomSocialButton};

CustomLink.propTypes = {
    href: propTypes.isRequired,
    className: propTypes.isRequired,
    children: propTypes.isRequired
}

CustomSocialButton.propTypes = {
    className: propTypes.isRequired,
    children: propTypes.isRequired,
}