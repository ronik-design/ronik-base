import React from 'react';
import parse from 'html-react-parser';

const ContentBlock = ({ title, description }) => {
    return (
        <div className="content-block">
             <div className="content-block__inner">
                <div className="content-block__content">
                    <h1>{parse(title)}</h1>
                    <p>{parse(description)}</p>
                </div>
             </div>
        </div>
    );
  };
  
  export default ContentBlock;