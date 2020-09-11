export class Video {
  constructor(
    public id: number,
    public user: {id: number, name: string, surname: string},
    public title: string,
    public description: string,
    public url: string,
    public status: string,
    public created_at: string,
    public updated_at: string
  ) {}
}
