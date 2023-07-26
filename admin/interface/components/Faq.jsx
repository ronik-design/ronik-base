import React, { useState, useEffect } from 'react';
import parse from 'html-react-parser';

const FAQ = ({ items }) => {
    const [isActive, setIsActive] = useState(-1);
    const faqActivator = (index) =>{
        if( isActive !== index){
            setIsActive(index);
        } else {
            setIsActive(-1);
        }
    }
    return (
        <div className="accordion">
            {items.map((item, index) => (
                <div className="accordion-item" key={index}>
                    <div
                        className="accordion-title"
                        onClick={() => faqActivator(index)}
                        >
                        <div>{parse(item[0])}</div>
                        <div>{isActive == index ? '-' : '+'}</div>
                    </div>
                    {isActive == index && <div className="accordion-content">{parse(item[1])}</div>}
                </div>
            ))}
        </div>
    );
  };
  
  export default FAQ;