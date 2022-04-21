import React, {Component} from 'react';
import {Modal} from 'antd';

import { withTranslation } from 'react-i18next';

import MachineInfo from '@app/web/machine/info/Main';

class DefaultResponse extends Component {
    constructor(props){
        super(props);
        this.state = {
            response: {...this.props.response}
        }
    }
    
    componentDidUpdate(prev) {
        if (prev.response.key != this.props.response.key) {
            this.setState({response: {...this.props.response}});
            if (this.props.response.success === false) {
                message.error(this.props.t(this.props.response.error));
            }            
        }
    }

    render() {
        return this.state.success === false ? null : ( this.state.response.data.map( (response, index) => {
            switch(Object.keys(response)[0]) {
                case 'machineInfo':
                    return <Modal 
                        key={index} 
                        visible={true} 
                        closable={true}
                        footer={null}
                        onCancel={() => this.setState(state => {
                            state.response.data.splice(index, 1);
                            return state;
                        })}
                        width="90%">
                        <MachineInfo info={response.machineInfo}/>
                    </Modal>;
            }
        }))
    }
}

export default withTranslation()(DefaultResponse);