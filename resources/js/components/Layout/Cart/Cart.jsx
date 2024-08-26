import React, { useEffect, useState } from "react";
import propTypes from "prop-types";
import { useDispatch } from "react-redux";
import ItemCart from "./ItemCart";
import { resetCart, deleteItem, increaseQuantity, decreaseQuantity} from "../../../redux/appSlicer";
import { useModal } from "../_partials/custom/ModalContext.jsx";

const Cart = ({ className,  products }) =>{
    const dispatch = useDispatch();
    const [totalAmount, setTotalAmount] = useState("");
    const {isOpen, closeModal} = useModal();
    
    useEffect(() => {
      let price =0;
      products.map((product) =>{
          price += product.price * product.quantity;
          return price;
      })

      setTotalAmount(price);
    }, [products])

   
    return (
      <div  className={`relative z-50 ${className} ${isOpen ? 'block opacity-100' : 'hidden opacity-0 '}`} aria-labelledby="slide-over-title"  role="dialog" aria-modal="true">
        {/* Background backdrop */}
        <div onClick={closeModal} className="fixed z-40 inset-0 bg-gray-500 bg-opacity-75 transition-opacity pointer-events-auto" aria-hidden="true" ></div>
        <div className={`fixed z-50 inset-0 overflow-hidden transform transition-transform ease-in-out duration-500 ${isOpen ? 'translate-x-0' : '-translate-x-full'}`}>
          <div className="absolute inset-0 overflow-hidden">
            <div className="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
              <div className="pointer-events-auto w-screen max-w-md">
                <div className="flex h-full flex-col overflow-y-scroll bg-white shadow-xl hover:custom-scrollbar">
                  <div className="flex- 1 overflow-y-auto px-4 py-6 sm:px-6">
                    <div className="flex items-start justify-between">
                      <h2 className="text-lg font-medium text-gray-900" id="slide-over-title">Shopping cart</h2>
                      <div className="ml-3 flex h-7 items-center">
                        <button onClick={closeModal}  type="button" className="relative -m-2 p-2 text-gray-400 hover:text-gray-500">
                          <span className="absolute -inset-0.5"></span>
                          <span className="sr-only">Close panel</span>
                          <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </div>
                    </div>
                    {products.length > 0 ? (
                      <div className="mt-8">
                        <div className="flow-root">
                          <ul role="list" className="-my-6 divide-y divide-gray-200">
                            {products.map((product) => {
                                let removeButton = (
                                  <button key={product.id} onClick={() => dispatch(deleteItem(product.id))} type="button" className="font-medium text-indigo-600 hover:text-indigo-500">
                                    Remove
                                  </button>
                                );

                                let Quantity = (
                                  <div class="mx-auto flex h-8 items-stretch text-gray-600">
                                    <button class="flex items-center justify-center rounded-l-md bg-gray-200 px-4 transition hover:bg-black hover:text-white" onClick={() => dispatch(decreaseQuantity(product))}>-</button>
                                    <div class="flex w-full items-center justify-center bg-gray-100 px-4 text-xs uppercase transition">{ product.quantity}</div>
                                    <button class="flex items-center justify-center rounded-r-md bg-gray-200 px-4 transition hover:bg-black hover:text-white" onClick={() => dispatch(increaseQuantity(product))}>+</button>
                                  </div>
                                )
  
                              return <ItemCart key={product.id} item={product} removeButton={removeButton} Quantity={Quantity} />;
                            })}
                          </ul>
                        </div>
                      </div>
                    ) : (
                      <div>Aucun element</div>
                    )}
                  </div>
  
                  <div className="border-t border-gray-200 px-4 py-6 sm:px-6">
                    <div className="flex justify-between text-base font-medium text-gray-900">
                      <p>Subtotal</p>
                      <p>${totalAmount}</p>
                    </div>
                    <p className="mt-0.5 text-sm text-gray-500">Shipping and taxes calculated at checkout.</p>
                    <div className="mt-6">
                      <a className="disabled flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700">
                        Checkout
                      </a>
                    </div>
                    <div className="mt-6 flex justify-center text-center text-sm text-gray-500">
                      <p>
                        or
                        <button onClick={() => dispatch(resetCart())} type="button" className="font-medium text-indigo-600 hover:text-indigo-500">
                          Continue Shopping
                          <span aria-hidden="true"> &rarr;</span>
                        </button>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        </div>
    );
}

export default Cart;

