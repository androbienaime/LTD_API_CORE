import React from "react";
import propTypes from "prop-types";
import { useModal } from "./ModalContext";

const OpenModalButton = ({ children }) => {
   const { openModal } = useModal();
    return (
    <div className="relative" onClick={openModal} >
        { children }
    </div>
    );
};
export { OpenModalButton };
OpenModalButton.propTypes = ({
    children : propTypes.isRequired
})