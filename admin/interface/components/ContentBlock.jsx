import React from 'react';
import parse from 'html-react-parser';

const ContentBlock = ({ title, description }) => {
    return (
        <div className="content-block">
             <div className="content-block--inner">
                <div className="content-block--wrap">
                    <h1>{parse(title)}</h1>
                    <p>{parse(description)}</p>
                </div>
             </div>
        </div>
    );
  };
  
  export default ContentBlock;