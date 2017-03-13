/*
Angular 2/Typescript Line Chart
 */
import {Component} from "@angular/core";

@Component({
	/*selector: "line-chart-draw",*/
	templateUrl: "./templates/line-template.php"

})

export class LineComponent {
	/* Fake line data, replace with link to actual data */
	public  lineChartData:Array<any> = [
		{data: [150, 325, 470, 531, 505, 828, 962, 1001, 1111, 1212, 1313], label: "Big Data"},
		{data: [15, 32, 47, 53, 50, 82, 96, 101, 111, 121, 131], label: "Small Data"}
	];
	/* Fake labels, replace with link to actual data */
	public lineChartLabels:Array<any> = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

	public lineChartOptions:any = {
		responsive: true,
		spanGaps: true
	};

	public lineChartColors:Array<any> = [
		{
			//red (chile?)
			backgroundColor: "rgba(247, 136, 156, 0.2)",
			borderColor: "rgba(255, 0, 0, 1)",
			pointBackgroundColor: "rgba(247,136,156, 1)",
			pointBorderColor: "rgba(255, 0, 0, 1)",
		},
		{
			//green (chile?)
			backgroundColor: "rgba(173, 255, 178, 0.2)",
			borderColor: "rgba(0, 255, 0, 1)",
			pointBackgroundColor: "rgba(173, 255, 178, 1)",
			pointBorderColor: "rgba(0, 255, 0, 1)",
		}
	];

	public lineChartLegend:boolean = true;
	public lineChartType:string = 'line';

}