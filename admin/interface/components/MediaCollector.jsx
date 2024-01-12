import React, { useState, useEffect, useRef } from 'react';
import parse from 'html-react-parser';

import { DndProvider } from 'react-dnd'
import { HTML5Backend } from 'react-dnd-html5-backend'

import { useDrag, useDrop } from 'react-dnd'


const MediaCollector = ({ items }) => {
    const [unPreserveImageId, setUnImageId] = useState([]);
    const [preserveImageId, setImageId] = useState([]);
    const targets = document.querySelectorAll("[data-media-id]");

    for (var i = 0, len = targets.length; i < len; i++) {
        targets[i].querySelector("button").addEventListener('click', (e) => {
            const f_wpwrap = document.querySelector("#wpwrap");
            const f_wpcontent = document.querySelector("#wpcontent");
            f_wpwrap.classList.add('loader')
            f_wpcontent.insertAdjacentHTML('beforebegin', '<div class= "centered-blob"><div class= "blob-1"></div><div class= "blob-2"></div></div>');

            if(e.target.getAttribute("data-unpreserve-media")){
                e.target.textContent = 'Row is Removed!';
                setUnImageId([e.target.getAttribute("data-unpreserve-media")]);
                // Lets remove the row.
                e.currentTarget.parentNode.parentNode.remove();
                return;
            }
            else if(e.target.getAttribute("data-preserve-media")) {
                e.target.textContent = 'Row is Removed!';
                setImageId([e.target.getAttribute("data-preserve-media")]);
                // Lets remove the row.
                e.currentTarget.parentNode.parentNode.remove();
                return;
            }
        });
    }

    useEffect(() => {
        console.log(preserveImageId);
        handlePostDataPreserve( preserveImageId, 'invalid');
    }, [preserveImageId]);
    useEffect(() => {
        handlePostDataPreserve( 'invalid' , unPreserveImageId);
    }, [unPreserveImageId]);


    const handlePostDataPreserve = async ( preserveImageId,  unPreserveImageId) => {
        const data = new FormData();
            data.append( 'action', 'rmc_ajax_media_cleaner' );
            data.append( 'nonce', wpVars.nonce );
            data.append( 'post_overide',  "media-preserve" );

            data.append( 'preserveImageId',  preserveImageId );
            data.append( 'unPreserveImageId',  unPreserveImageId );

        fetch(wpVars.ajaxURL, {
            method: "POST",
            credentials: 'same-origin',
            body: data
        })
        .then((response) => response.json())
        .then((data) => {
            if (data) {
                console.log(data);
                if(data.data == 'Reload'){
                    setTimeout(function(){
                        // Lets remove the form
                        location.reload();
                    }, 1000);
                }
            }
        })
        .catch((error) => {
            console.log('[WP Pageviews Plugin]');
            console.error(error);
        });
    }




    const PetCard = ({ id, name }) => {
        const [{ isDragging }, dragRef] = useDrag({
            type: 'pet',
            item: { id, name },
            end: (item, monitor) => {
                const dropResult = monitor.getDropResult()
                if (item && dropResult) {
                  alert(`You dropped ${item.name} into ${dropResult.name}!`)
                }
            },
            collect: (monitor) => ({
                isDragging: monitor.isDragging(),
                handlerId: monitor.getHandlerId()

            })
        })
        return (
            <div id="id" className='pet-card' ref={dragRef}>{name}{isDragging && '😱'}</div>
        )
    }






    var el = document.querySelectorAll("[data-class=image-target]");
    const IMGARRAYS = [];
    for (let i = 0; i < el.length; i++) {  
        IMGARRAYS[i] = {
            id: el[i].getAttribute('data-id'), 
            name: 'dog',
            src: el[i].getAttribute('src')
        }
    }






    const PETS = [
        { id: 1, name: 'dog' },
        { id: 2, name: 'cat' },
        { id: 3, name: 'fish' },
        { id: 4, name: 'hamster' },
    ]
    
    const styles = {
        height: '12rem',
        width: '12rem',
        marginRight: '1.5rem',
        marginBottom: '1.5rem',
        color: 'white',
        padding: '1rem',
        textAlign: 'center',
        fontSize: '1rem',
        lineHeight: 'normal',
        float: 'left',
      }

    const Basket = () => {
        const [basket, setBasket] = useState([])
        const [{ canDrop, isOver }, dropRef] = useDrop({
            accept: 'pet',
            drop: () => ({ name: 'Dustbin' }),
            drop: (item) => setBasket((basket) => !basket.includes(item) ? [...basket, item] : basket),
            collect: (monitor) => ({
                isOver: monitor.isOver(),
                canDrop: monitor.canDrop(),

            })
        })
        const isActive = canDrop && isOver
        let backgroundColor = '#222'
        if (isActive) {
          backgroundColor = 'darkgreen'
        } else if (canDrop) {
          backgroundColor = 'darkkhaki'
        }
        return (
            <React.Fragment>
                <div className='pets'>
                    {IMGARRAYS.map(pet => <PetCard draggable id={pet.id} name={pet.src} />)}
                </div>
                <div className='basket' ref={dropRef} style={{  ...styles, backgroundColor }}>
                    {basket.map(pet => <PetCard id={pet.id} name={pet.name} />)}
                    {isOver && <div>Drop Here!</div>}
                </div>
            </React.Fragment>
        )
    }







      
    return (

        <>
            <div className="message"> </div>
            <div>
            <DndProvider backend={HTML5Backend}>
                {/* Here, render a component that uses DND inside it */}
                <Basket />
            </DndProvider>
            </div>
        </>
        
    );
  };
  
  export default MediaCollector;