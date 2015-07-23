#include <wiringPi.h>
#include <stdio.h>
#include <stdlib.h>
#include <stdint.h>
#include <time.h>
#define MAX_TIME 85
#define DHT11PIN 7
int dht11_val[5]={0,0,0,0,0};

void dht11_read_val()
{
	uint8_t lststate=HIGH;
	uint8_t counter=0;
	uint8_t j=0,i;
	float farenheit;
	for(i=0;i<5;i++)
		dht11_val[i]=0;
		pinMode(DHT11PIN,OUTPUT);
		digitalWrite(DHT11PIN,LOW);
		delay(18);
		digitalWrite(DHT11PIN,HIGH);
		delayMicroseconds(40);
		pinMode(DHT11PIN,INPUT);
		for(i=0;i<MAX_TIME;i++)
		{
			counter=0;
			while(digitalRead(DHT11PIN)==lststate){
				counter++;
				delayMicroseconds(1);
				if(counter==255)
				break;
			}
		lststate=digitalRead(DHT11PIN);
		if(counter==255)
		break;
		// top 3 transistions are ignored  
		if((i>=4)&&(i%2==0)){
			dht11_val[j/8]<<=1;
			if(counter>16)
			dht11_val[j/8]|=1;
			j++;
        }
      }  
		// verify cheksum and print the verified data  
		if((j>=40)&&(dht11_val[4]==((dht11_val[0]+dht11_val[1]+dht11_val[2]+dht11_val[3])& 0xFF)))
		{
			farenheit=dht11_val[2]*9./5.+32;
			time_t t;
			struct tm *tm;
			t=time(NULL);
			tm=localtime(&t);
			//printf("Humidity = %d.%d %% Temperature = %d.%d *C (%.1f *F)\n",dht11_val[0],dht11_val[1],dht11_val[2],dht11_val[3],farenheit);
			printf("%02d-%02d-%d %02d:%02d:%02d %d.%d %d.%d\n",tm->tm_mday,1+tm->tm_mon,1900+tm->tm_year,tm->tm_hour,tm->tm_min,tm->tm_sec,dht11_val[2],dht11_val[3],dht11_val[0],dht11_val[1],farenheit);
			exit(1);
		}
		else  
		//	printf("Invalid Data!!\n");
			printf("");
		}


int main(void)  
	{
		//printf("Interfacing Temperature and Humidity Sensor (DHT11) With Raspberry Pi\n");
		if(wiringPiSetup()==-1)
			exit(1);
			while(1)
			{
				dht11_read_val();
				delay(1000);
			}
			return 0;
    }