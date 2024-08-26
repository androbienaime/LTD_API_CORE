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

const CustomLi = ({className, children}) => {
    return (
        <li className={`${className} max-lg:border-b max-lg:px-3 max-lg:py-3`}>{children}</li>
    )
}

export {CustomLink, CustomLi};

CustomLink.propTypes = {
    href: propTypes.isRequired,
    className: propTypes.isRequired,
    children: propTypes.isRequired
}

CustomLi.propTypes = {
    className: propTypes.isRequired,
    children: propTypes.isRequired,
}