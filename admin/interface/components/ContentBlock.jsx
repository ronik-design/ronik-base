import React from 'react';
import parse from 'html-react-parser';

const ContentBlock = ({ className="", title, description }) => {
    return (
        <div className={`content-block ${className}`}>
             <div className="content-block__inner">
                <div className="content-block__content">
                    {title && <h1>{parse(title)}</h1>}
                    {description && <p>{parse(description)}</p>}
                </div>
             </div>
        </div>
    );
  };
  
  export default ContentBlock;